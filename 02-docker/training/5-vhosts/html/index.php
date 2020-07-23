<?php // index.php
if(isset($_GET['q'])) echo $_GET['q'];
else echo 'qが渡されていません';
?>