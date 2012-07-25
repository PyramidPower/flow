<?php
/**
 * Abstract class describing types of
 * Tasks
 *
 */
abstract class TaskType {
	var $w;
	
	function __construct(Web $w) {
		$this->w = $w;
	}
	function getTaskTypeTitle(){}
	function getTaskTypeDescription() {}
	
	/**
	 * return an array similar to the Html::form
	 * which describes the fields available for this
	 * task type and the way they should be presented in
	 * task details.
	 * 
	 */
	function getFieldFormArray() {}
	/**
	 * Executed before a task is inserted into DB
	 * 
	 * @param Task $task
	 */
	function on_before_insert($task) {}	
	/**
	 * Executed after a task has been inserted into DB
	 * 
	 * @param Task $task
	 */
	function on_after_insert($task) {}	
	/**
	 * Executed before a task is updated in the DB
	 * 
	 * @param Task $task
	 */
	function on_before_update($task) {}	
	/**
	 * Executed after a task has been updated in the DB
	 * 
	 * @param Task $task
	 */
	function on_after_update($task) {}	
	/**
	 * Executed before a task is deleted from the DB
	 * 
	 * @param Task $task
	 */
	function on_before_delete($task) {}	
	/**
	 * Executed after a task has been deleted from the DB
	 * 
	 * @param Task $task
	 */
	function on_after_delete($task) {}
	/**
	 * Return a html string which will be displayed alongside
	 * the generic task details.
	 * 
	 * @param Task $task
	 */
	function displayExtraDetails($task) {}
	
}
