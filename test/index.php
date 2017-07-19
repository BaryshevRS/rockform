<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="robots" content="noindex, nofollow" />
    <title>Тестирование</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" type="text/css" rel="stylesheet" />

  <style>
 
    .error {
       background:  red;
      padding: 10px 20px;
      margin-bottom: 15px;
      color: #fff;
      min-width: 250px;
      border-radius: 4px;
      font-family: arial;
      font-size: 16px;
    }
    .success {
       background: green;
      padding: 10px 20px;
      margin-bottom: 15px;
      color: #fff;
      min-width: 250px;
      border-radius: 4px;
      font-family: arial;
      font-size: 16px;
    }
    
  </style>


<!-- Rockform -->
 

<?php if(isset($_GET['alt'])) { ?> 
 
<!-- Rockform -->
    <link href="/rockform/core/themes/default/main.css" type="text/css" rel="stylesheet" />
    <script src="/rockform/core/frontend/jquery.min.js"></script>
    <script src="/rockform/core/frontend/jquery.mask.min.js"></script>
    <script src="/rockform/core/frontend/jquery.form.min.js"></script>
    <script src="/rockform/core/frontend/jquery.rtooltip.js"></script>
    <script src="/rockform/core/frontend/jquery.rmodal.js"></script>
    <script src="/rockform/core/frontend/baseform.js"></script>
<!-- // Rockform -->

<?php } else { ?>
 
    <script data-main="/rockform/core/frontend/app" src="/rockform/core/frontend/jquery.min.js"></script>
 
   <link href="/rockform/core/themes/default/main.css" type="text/css" rel="stylesheet" />
    <script data-main="/rockform/core/frontend/app" src="/rockform/core/frontend/require.min.js"></script>
 <?php } ?>
<!-- // Rockform -->
 
    <script src="/rockform/test/frontend/template/js/dinamic.js"></script>
</head>

<body>
    <div class="container">
<div class="page-header">
<h1>Rockform тесты</h1>


<ol>
  
  <li> Проверка валидации </li>
   <li>Проверка событий</li>
   <li>Проверка появления окна успешного выполнения</li>
     <li>Проверка таймера</li>
      <li>Проверка кнопки закрытия</li>
          <li>Проверка в разных браузерах</li>

              <li>Проверка в разных версиях php</li>
              <li>Проверка конфликтов с jquery</li>
</ol>
 </div>

<a href="" data-bf='{config: "xxx"}' class="x">test</a>

<?php
ini_set('error_reporting', E_ALL);
ini_set ('display_errors', 1);

header('Content-type: text/html;  charset=utf-8');

include_once $_SERVER['DOCUMENT_ROOT'].'/rockform/test/model/test.class.php';
$test = new Test();
 
$test->init();

$frontend = scandir(BF_PATH.'/test/frontend/');

foreach ($frontend as $file) {
  if(preg_match('~\.html~', $file)) {
?>
<div style="display: inline-block; width: 100%;">
<?php
    include(BF_PATH.'/test/frontend/'.$file);
?>
  </div> 
<?php 
  }


}

?>
 
 
       <br> 
        <br>  <br>  <br>  
    </div>
    <!-- /container -->
</body>

</html>
 