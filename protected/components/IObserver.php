<?php
/**
 * @desc 观察者接口
 * @author Administrator
 *
 */
interface IObserver
{
    /**
     * @desc 更新方法
     * @desc $entity multi
     */
    public function update($entity);
}