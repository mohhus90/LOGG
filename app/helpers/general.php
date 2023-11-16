<?php
function get_data_where($model,$col=array(),$where=array(),$order_by="id",$order_type="DESC",$pagination=13){
    $data = $model::select($col)->where($where)->orderby($order_by,$order_type)->paginate($pagination);
    return $data;
}