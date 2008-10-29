<?php

require 'drop_io_api.php';

$create = new DROPIO_CreateDrop();

$drop1=$create->send();
echo "<h3>CreateDrop</h3>";
echo "<a href='http://drop.io/".$drop1->name."'>drop #".$drop1->name."</a>";
echo "<pre>";
print_r($drop1);
echo "</pre>";


$get = new DROPIO_GetDrop();
$get->name=$drop1->name;

$drop2=$get->send();
echo "<h3>GetDrop</h3>";
echo "<a href='http://drop.io/".$drop2->name."'>drop #".$drop2->name."</a>";
echo "<pre>";
print_r($drop2);
echo "</pre>";


$update = new DROPIO_UpdateDrop();
$update->name=$drop1->name;
$update->token=$drop1->admin_token;
$update->expiration_length='1_DAY_FROM_NOW';
$update->privacy_type='VIEW';

$drop3=$update->send();
echo "<h3>UpdateDrop</h3>";
echo "<p>Change permissions and expiration</p>";
echo "<a href='http://drop.io/".$drop3->name."'>drop #".$drop3->name."</a>";
echo "<pre>";
print_r($drop3);
echo "</pre>";


$drop4=$get->send();
echo "<h3>GetDrop</h3>";
echo "<a href='http://drop.io/".$drop4->name."'>drop #".$drop4->name."</a>";
echo "<pre>";
print_r($drop4);
echo "</pre>";



$create = new DROPIO_CreateAsset_Link();
$create->drop_name=$drop1->name;
$create->token=$drop1->admin_token;
$create->url='http://slawcup.com';

$asset1=$create->send();
echo "<h3>CreateAsset_Link</h3>";
echo "<a href='http://drop.io/".$drop1->name."/asset/".$asset1->name."'>drop #".$drop1->name." [".$asset1->name."]</a>";
echo "<pre>";
print_r($asset1);
echo "</pre>";

$create = new DROPIO_CreateAsset_Link();
$create->drop_name=$drop1->name;
$create->token=$drop1->admin_token;
$create->url='http://twitter.com/slawcup';

$asset1=$create->send();
echo "<h3>CreateAsset_Link</h3>";
echo "<a href='http://drop.io/".$drop1->name."/asset/".$asset1->name."'>drop #".$drop1->name." [".$asset1->name."]</a>";
echo "<pre>";
print_r($asset1);
echo "</pre>";



$create = new DROPIO_CreateAsset_Note();
$create->drop_name=$drop1->name;
$create->token=$drop1->admin_token;
$content='';
for($i=0;$i<20;$i++){
	$content.="Test Asset Note (line #$i)<br/>";
}
$create->contents=$content;
$create->title='test asset note '.microtime(true);

$asset2=$create->send();
echo "<h3>CreateAsset_Link</h3>";
echo "<a href='http://drop.io/".$drop1->name."/asset/".$asset2->name."'>drop #".$drop1->name." [".$asset2->name."]</a>";
echo "<pre>";
print_r($asset2);
echo "</pre>";

$get = new DROPIO_GetAssets();
$get->drop_name=$drop1->name;

$assetsList=$get->send();
echo "<h3>GetAssets</h3>";
echo "<a href='http://drop.io/".$drop1->name."'>drop #".$drop1->name."</a>";
echo "<pre>";
print_r($assetsList);
echo "</pre>";


?>