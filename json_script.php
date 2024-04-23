<?php
	function collectDataSetParam()
	{
		if(isset($_REQUEST['DataSet']))
			return $_REQUEST['DataSet'];
	}
		
	$param = collectDataSetParam();
	echo("in php");
	switch($param)
	{
		case "DataSet5" :
			echo json_encode(array(
							array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec"),
							array(
								array(
									"name"=>"User 1",
									"data"=>array(
											12,10.5,12,8.5,15,16.8,17.2,9.6,13.7,12.4,11,19.2
										)
								),
								array(
									"name"=>"User 2",
									"data"=>array(
											18.5,16,9,11.9,12,17.6,15.6,13.7,13.5,17.3,15.3,11.6
										)
									),
								array(
									"name"=>"User 3",
									"data"=>array(
											14.2,15.6,5.12,12.8,13.6,15.6,14.3,15.8,13.6,15.2,16.2,5.6
										)
									)
								)
					)	);
			break;
		case "DataSet4" :
			echo json_encode(array(
							array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec"),
							array(
								array(
									"name"=>"User 1",
									"data"=> array(
											5,1.5,8,15.3,11,7.2,1.9,9.6,13.8,10.8,5.3,8.2
										)
								),
								array(
									"name"=>"User 2",
									"data"=>array(
											1.5,10,9,12.9,15,10.6,5.6,9.7,11.5,11.3,8.3,10.5
										)
								),
								array(
									"name"=>"User 3",
									"data"=>array(
											10.2,12.8,9.8,7.6,6.9,11.2,11,12.9,11.7,9.8,13.2,15.6
										)
								)
							)
					)	);
			break;
		case "DataSet3" :
			echo json_encode(array(		
							array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec"),
							array(
								array(
									"name"=>"User 1",
									"data"=> array(
											12,6.7,12,12.3,9,15.2,10.9,12.6,10.2,12.3,14.3,9.6
										)
								),
								array(
									"name"=>"User 2",
									"data"=>array(
											10,10.8,7,15.9,14,12.6,15.6,15.7,12.5,12.3,10.3,12.5
										)
								),
								array(
									"name"=>"User 3",
									"data"=>array(
											9.2,14.8,12.6,13.6,12.9,12.2,13,12.9,14.7,12.8,9.2,12.6
										)
								)
							)
					)	);
			break;
		case "DataSet2" :
			echo json_encode(array(
							array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec"),
							array(
								array(
									"name"=>"User 1",
									"data"=>array(
											10,6.7,16,11.5,7,11.2,12.8,15.4,7.2,8.3,10.3,11.6
										)
								),
								array(
									"name"=>"User 2",
									"data"=>array(
											12,15.8,9,11.9,15,12.4,14.6,12.7,13.2,10.6,15.1,7.5
										)
								),
								array(
									"name"=>"User 3",
									"data"=>array(
											11.2,10.8,16.6,10.6,7.9,9.2,6,11.9,12.7,11.2,12.2,8.6
										)
								)
							)
					)	);
			break;
		default :
			echo json_encode(array(
							array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec"),
							array(
								array(
									"name"=>"User 1",
									"data"=>array(
											8,11.7,15,10.9,5.9,11.2,6.9,10.5,12.9,13.3,10.3,14.2
										)
									),
								array(
									"name"=>"User 2",
									"data"=>array(
											8.6,15.8,9,11.9,14.8,11.6,11.6,12.1,15.6,13.3,12.3,15.5
										)
									),
								array(	
									"name"=>"User 3",
									"data"=>array(
											9.6,15.8,10.5,9.5,10.8,10.7,8,11.5,13.9,15.2,14.2,11.6
										)
									)
							)
					)	);
			break;
	}


?>
