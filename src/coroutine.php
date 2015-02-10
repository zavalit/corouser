<?php
require_once __DIR__ . "/Schedule.php";

$scheduler = new Schedule();

function down(){ 
  $i=10;
  while($i-->0){
    yield "while down $i";
  }
}
$scheduler->addTask(down());

function up(){ 
  $i=0;
  while($i++<10){
    yield "while up $i";
  }
};
$scheduler->addTask(up());

foreach($scheduler->run() as $row){
  echo $row . PHP_EOL;
};
