<?php
class OrderLogObserver implements IObserver
{
    public $orderLogModel = null;
    
    public $filterField = ['opration_id', 'opration_date', 'modify_time', 'modify_id'];
    
    public function __construct()
    {
        $this->orderLogModel = new OrderUpdateLog();
    }
    
    public function update($entity)
    {
        if (!$entity instanceof Order)
            return false;
        $content = '';
        $oldAttributes = $entity->oldAttributes;
        $newAttributes = $entity->attributes;
        foreach ($newAttributes as $field => $value)
        {
            if (in_array($field, $this->filterField))
                continue;
            $oldValue = null;
            if (array_key_exists($field, $oldAttributes))
                $oldValue = $oldAttributes[$field];
            $fieldLabel = $entity->getAttributeLabel($field);
/*             if ($oldValue === null)
            {
                $content .= '新增' . $fieldLabel . '值为 ' . $value;
            } */
            if ($value != $oldValue)
            {
                $content .= $fieldLabel . '由 ' . $oldValue . '修改为' . $value . ';';
            }
        }
        $orderId = $entity->order_id;
        $updateBy = $entity->update_by;
        if ($content != '')
        {
            $this->orderLogModel->order_id = $orderId;
            if (isset($this->orderLogModel->update_by) && empty($this->orderLogModel->update_by))
                $this->orderLogModel->create_by = $updateBy;
            else if (isset(Yii::app()->user) && !empty(Yii::app()->user))
            {
                $this->orderLogModel->create_by = Yii::app()->user->user_name;
                $this->orderLogModel->create_user_id = Yii::app()->user->id;
            }
            $this->orderLogModel->update_content = $content;
            $this->orderLogModel->create_time = date('Y-m-d H:i:s');
            return $this->orderLogModel->save(false);
        }
        return false;
    }
}