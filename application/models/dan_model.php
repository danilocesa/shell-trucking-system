<?php
class Dan_model extends CI_Model
{
		
/*************** Select data ****************************/		
		function select_where_c_o($table,$where,$order)
		{
			return $this->db
				->order_by($order,'desc')
				->where($where)
				->get($table)
				->result();
		}
		
		function select_only_limit($table) //Select all recent added content with limit 5 
		{
			return $this->db
			->get($table,1)
			->result();
		}
		
	
		function select_where_not_in_row($table,$col,$val) //Select table
		{
		  return $this->db
		  ->where_not_in($col, $val)
		  ->get($table)
		  ->row();
		}
		
		function select_recent_result($table,$where,$column,$limit)//DIVINA
		
		{
			return $this->db
				->order_by($column,'desc')
				->where($where)
				->get($table,$limit)
				->result();
		}
		
		
		function select_table($table) //Select table
		{
			return $this->db
			->get($table)
			->result();
		}
		
		//create by divina
		function selectall_orderby($table,$order)
		{
			return $this->db
			->order_by($order, "desc")
			->get($table)
			->result();
		}
		
		
		function select_where($table,$where) // select wher return  2 dimensional array
		{
			return $this->db
				->where($where)
				->get($table)
				->row();
		}	

		function select_where_or($table,$where,$where1) // select wher return  2 dimensional array
		{
			return $this->db
				->where($where)
				->or_where($where1)
				->get($table)
				->row();
		}	
		
		function select_result($table,$where) //Select all with where condition return array
		
		{
			return $this->db
				->where($where)
				->get($table)
				->result();
		}
		
		function select_like($table, $AA, $value) //divibs
		
		{
			return $this->db
				->like($AA, $value)
				->get($table)
				->row();
		}
		
		
		
		function select_where_c_order($table,$where,$order) //Select all with where condition return array
		{
			return $this->db
				->select("")
				->order_by($order,'desc')
				->where($where)
				// ->group_by('po_header_id')
				->get($table)
				->result();
		}
		
		function select_max($table,$max)
		{
			return $this->db
			->select_max($max)
			->get($table)
			->row();
		}

		function select_where_in($table,$col,$where){
			return $this->db
				->where_in($col,$where)
				->get($table)
				->result();
		}
		
/***************************************/	

/**************Count**********************/
	function count_where($table,$where)
		{
			return $this->db
				->where($where)
				->get($table)
				->num_rows();
		}
	function count_table($table)
		{
			return $this->db
				->get($table)
				->num_rows();
		}	
		
	function count_where_or($table,$where,$where1)
		{
			return $this->db
				->where($where)
				->or_where($where1)
				->get($table)
				->num_rows();
		}	
		
	/****************sums**************/	
	function sum($table,$where,$column)
		{
			return $this->db
				->select_sum($column)
				->where($where)
				->get($table)
				->num_rows();
		}
		
	function sums($column,$where,$table)
		{
			  	$query = $this->db->select_sum($column);
    			$query = $this->db->where($where);
				$query = $this->db->get($table);	
    			$result = $query->result();
				return $result[0]->rating_value;

		}			
		
		
		
	/********For Pagination*********/
	function record_count($table) {
	        return $this->db->count_all($table);
	    }
	

/************Insert**********************/
	function inserting($table,$to_insert)
	{
		return $this->db
			->insert($table,$to_insert);
	}
	
	
	
	function insert_batch($table,$many_insert)
	{
		return $this->db->insert_batch($table,$many_insert);
	}
	
/************************************/

/*************Delete*********************/
	function delete_where($table,$where)
		
		{
			return $this->db
			->where($where)
			->delete($table);
		}
/*********************************/




/********************Update****************************/		
		
		function update_where($table,$to_update)// update single
		
		{
			return $this->db
				->update($table,$to_update);
			
		}
		
		
		function update_all_c($table,$to_update,$where) // update where
		{
		return $this->db
			->where($where)
			->update($table,$to_update);
		}	
	
}

?>