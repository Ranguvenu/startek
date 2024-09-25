<?php
require_once(dirname(__FILE__) . '/../../config.php');
use local_custom_matrix\local\local_custom_matrix_store;


$store = new local_custom_matrix_store();


$matrix = required_param('data', PARAM_RAW);
$type = required_param('type', PARAM_RAW);

$encode_data = utf8_encode($matrix); // Don't forget the encoding
$matrixarr = json_decode($encode_data);

$response = [];

foreach($matrixarr as $matrix){

	if($type == 1){
		if($matrix->id != 0){
			$result = $store->upadte_matrix($matrix);
			$response['message'] = 'Data Updated Successfully';
		}else{
			$result = $store->insert_matrix($matrix);
			$response['message'] = 'Data Saved Successfully';
		}		
	}else{
		if($matrix->id != 0){
			$result = $store->upadte_overall_matrix($matrix);
			$response['message'] = 'Data Updated Successfully';
		}else{
			$result = $store->insert_overall_matrix($matrix);
			$response['message'] = 'Data Saved Successfully';
		}	

	}
	
	
}


echo json_encode($response);

