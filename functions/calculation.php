<?php

require_once '../config/DB.php';

//$wall varibles

$sensible_load = array(0,0,0,0,0,0,0,0,0,0);

$wall_area = (double)0; //Will be the 'a' parameter
$wall_u_value = (double)0;

$wall_id;
$wall_width = (double)0;
$wall_height = (double)0;
$wall_thickness = (double)0;
$wall_type;
$wall_direction;
$wall_int_tem = (double)0;
$wall_ext_tem = (double)0;
$wall_k_val = (double)0;

//$window and $door
$window_area = (double)0;
$door_area = (double)0;

//$constants
$h0 = (double)1;
$h1 = (double)1;

//CLTB Values
$num_cltb = 10;


// Check connection

//Sensible load calculation - wall - start
//To find out how many number of wall details are entered
$sql = "SELECT * FROM tbl_wall";
$result = $DB->prepare($sql);

$result->execute();
$result = $result->fetchAll(PDO::FETCH_ASSOC);
$i=0;
while($i<sizeof($result)){

    $wall_id = $result[$i]['wall_id'];
    $wall_height = $result[$i]['height'];
    $wall_width = $result[$i]['width'];
    $wall_int_tem = $result[$i]['int_temp'];
    $wall_ext_tem = $result[$i]['ext_temp'];
    $wall_type = $result[$i]['is_sunlit'];
    $wall_thickness = $result[$i]['thickness'];
    $wall_direction = $result[$i]['direction'];
    $wall_k_val = $result[$i]['k_val'];
    print($wall_id);

    //calculate the wall area.
    $wall_area = $wall_height*$wall_width;

    $sql_window = "SELECT height, width  FROM tbl_window WHERE wall_id = :wall_id";

    $stmt = $DB->prepare($sql_window);
    $stmt->bindParam(':wall_id', $wall_id);
    $stmt->execute();

    $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = $stmt;
    $j=0;
    while($j < sizeof($result)) {
        $window_width = $result[$j]['width'];
        $window_height = $result[$j]['height'];
        $window_area += $window_height*$window_width;

        $j++;
    }
    $sql_door= "SELECT height, width  FROM tbl_door WHERE wall_id = :wall_id";

    $stmt = $DB->prepare($sql_door);
    $stmt->bindParam(':wall_id', $wall_id);
    $stmt->execute();
    $result = $stmt;
    $result = $result->fetchAll(PDO::FETCH_ASSOC);
    $j=0;
    while($j < sizeof($result)) {
        $door_width = $result[$j]['width'];
        $door_height = $result[$j]['height'];
        $door_area += $door_height*$door_width;

        $j++;
    }
    $wall_area -= ($door_area+$window_area);
    $wall_u_value = (1/$h0) + ($wall_thickness/$wall_k_val) + (1/$h1);

    if($wall_type == "Sunlit-YES"){
        //Obtain the CLTD values using a loop and do the calculation for each time value
        $sql_cltb_wall= "SELECT * FROM cltb_wall WHERE direction = :wall_direction";
        $stmt = $DB->prepare($sql_cltb_wall);
        $stmt->bindParam(':wall_direction', $wall_direction);
        $stmt->execute();
        
        //Obtain all the values with respect to the passed direction and insert in the $sensible_load array
        
    }

    else{
        
        for($x = 0; $x < 10; $x++) {
           $sensible_load[$x] += ($wall_area * $wall_u_value * ($wall_ext_tem-$wall_int_tem));//Sally metana int-ext nathanm anik pathada?? Abs ganna weida??
        }
    }

    $i++;
}

//Sensible load calculation - wall - done



$url = "../results.html";

header("Location: $url");
?>