<?
session_start();

function GetRealIp()

{

 if (!empty($_SERVER['HTTP_CLIENT_IP'])) 

 {

   $ip=$_SERVER['HTTP_CLIENT_IP'];

 }

 elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))

 {

  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];

 }

 else

 {

   $ip=$_SERVER['REMOTE_ADDR'];

 }

 return $ip;

}


$url = $_SERVER['REQUEST_URI'];

$url[0] = '';

$parentUrl =   substr($url, 1, strpos($url , '/'));
$parentUrl = str_replace('/', '', $parentUrl);

$url = substr($url, strpos($url , '/'), strlen($url));

$url = str_replace('/', '', $url);


try {
	$dbh = new PDO('mysql:host=localhost;dbname=ch81240_u6878947', 'ch81240_u6878947', 'xVsXJ0l3jv1', array(PDO::ATTR_PERSISTENT => true));
	$dbh->exec('SET NAMES utf8'); 

	$sql = 'SELECT id, name, url, categoryID, preview, timeUpdate FROM topik WHERE url = ?';
	$sth =  $dbh->prepare($sql);

	$sth->execute(array($parentUrl));

	$rowsParentList = $sth->fetchAll();

       $previewShare = $rowsParentList [0][preview];

        $categoryID = $rowsParentList[0]['categoryID'];


        $sql = 'SELECT name, url, nameNav  FROM category WHERE id = ?';
	$sth =  $dbh->prepare($sql);
	$sth->bindParam(1, $categoryID);
	$sth->execute();
	$categoryName = $sth->fetchAll();

	$sql = 'SELECT id, name, timeUpdate FROM list WHERE urlList = ? AND idparent = ?';
	$sth =  $dbh->prepare($sql);

	$sth->bindParam(1, $url);
              $sth->bindParam(2, $rowsParentList[0]['id']);

	$sth->execute();

	$rowsList = $sth->fetchAll();

$LastModified_unix =strtotime( $rowsParentList[0]['timeUpdate']); // время последнего изменения страницы
$LastModified = gmdate("D, d M Y H:i:s \G\M\T", $LastModified_unix);
$IfModifiedSince = false;
if (isset($_ENV['HTTP_IF_MODIFIED_SINCE']))
    $IfModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));  
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
    $IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
if ($IfModifiedSince && $IfModifiedSince >= $LastModified_unix) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
}
header('Last-Modified: '. $LastModified);

        $sql = 'SELECT  comment.id as id, comment.comment as comment, comment.rating as rating FROM comment WHERE idparent = ? ORDER BY rating DESC LIMIT 3';
	$sth =  $dbh->prepare($sql);

	$sth->execute(array($rowsList[0]['id']));

	$rowsCommentTop = $sth->fetchAll();

	$sql = 'SELECT users.url as userUrl, users.name as userName, comment.id as id, comment.comment as comment, comment.rating as rating FROM comment LEFT JOIN users ON comment.userID = users.id WHERE comment.idparent = ? AND comment.id NOT IN ('.$rowsCommentTop[0][id].','.$rowsCommentTop[1][id].','.$rowsCommentTop[2][id].') ORDER BY comment.id DESC ';
	$sth =  $dbh->prepare($sql);

	$sth->execute(array($rowsList[0]['id']));

	$rowsComment = $sth->fetchAll();

        $title =  'Отзывы на '.$rowsList[0]['name'];

        $desc = 'Посмотри все отзывы на '.$rowsList[0]['name'];
	include ("../../header.php");

}
catch(Exception $e) {
	$dbh = null;
	header('Location: http://thetop10.ru/');
	exit;
}
?>

<style>
h1{
margin-top:55px!important;
}
h2 a {
    color: #F2A30B!important;
    font-size: 15px!important;
    font-weight: 400!important;
}
h2 a:hover {
    text-decoration: underline!important;
}

h2 {
    color: #919399!important;
    font-size: 11px!important;
    padding: 0!important;
}
.listitem{
margin-top:20px!important;
}
.social-buttons{
position:relative;
}
.social-buttons-div{
margin-bottom:12px;
width:600px;
}
#vkshare0{
float: left;
}
.twitter-share-button{
margin-left:10px!important;
margin-bottom: 5px!important;
}
#___plus_0{
margin-left:5px!important;
}
.commentsig{
margin-left: 20px;
margin-top: -10px;
}
.listitem{
padding:0!important;
}
.listitem{
padding:0!important;
}
.negative{
color: #F2A30B!important;
}

</style>
<link rel="image_src" href="http://thetop10.ru/img/<?=$previewShare?>">
<div id="crumbs"><a class="bhome" href="http://thetop10.ru/">Главная</a>»<a href="http://thetop10.ru/vse-topy/">Все топы</a>»<a href="http://thetop10.ru/<?=$categoryName[0][url];?>/"><?=$categoryName[0][nameNav]?></a></div>
<div>
<h1>Отзывы на <?=$rowsList[0][name];?></h1>
<div style = "text-align:center!important;">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- баннер1 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-3388378408783637"
     data-ad-slot="7419599100"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
<h2><a href="http://thetop10.ru/<?=$rowsParentList[0][url];?>"><?=$rowsParentList[0][name];?></a> « Отзывы о  <?=$rowsList[0][name];?></h2>
</div>

<div class="listitem">
<h3>Отзывы на  <?=$rowsList[0][name];?></h3>
<br>
<div class="theitem">
<?
$x = 0;
$countCommentaries = count($rowsComment);
while($x<$countCommentaries):?>
<span>
<div class="comment" thumbs="<?=$rowsComment[$x]['rating']?>">
<span class="openq"><b id = "openqq<?=$rowsComment[$x]['id'];?>" <?if($rowsComment[$x]['rating']<0):?>class = "negative"<?endif;?>><?if($rowsComment[$x]['rating']!=0):?><?echo $rowsComment[$x]['rating'];?><?endif;?></b></span>
<?echo nl2br($rowsComment[$x]['comment']);?><div class="commentlinks">
<div style="width: 580px; display: block; margin-top: 33px;" class="slider"></div>
<div style="width: 580px; display: block;" class="slidercontent"><span onclick = "plus(<?=$rowsComment[$x]['id'];?>)" class="tup"  id ="tup<?=$rowsComment[$x]['id'];?>" style="margin-top: 33px;">+1</span><span onclick = "minus(<?=$rowsComment[$x]['id'];?>)" class="tdown"  id ="tdown<?=$rowsComment[$x]['id'];?>" >-1</span></div>
</div>
</div>
</span>
<?if(isset($rowsComment[$x]['userUrl'])):?>
<p class="commentsig"><b><a href = "http://thetop10.ru/user/<?=$rowsComment[$x]['userUrl'];?>"><?echo $rowsComment[$x]['userName'];?></a></b></p>
<?endif;?>
<?
if($x == (int)($countCommentaries/2)):?>
<div class="listspacer"></div>
<div>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- баннер2 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-3388378408783637"
     data-ad-slot="4326531906"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
<div class="listspacer"></div>
<?endif;?>
<?
$x++;
endwhile;?>
</div>
<br clear="all">
</div>
<?

try {

	$sql = 'SELECT DISTINCT topik.name, topik.url FROM listLinkTopik INNER JOIN topik ON topik.id = listLinkTopik.idTopik WHERE idTopik IN (SELECT idTopik FROM listLinkTopik WHERE idList = ?)';
	$sth =  $dbh->prepare($sql);

	$sth->execute(array($rowsList [0]['id']));

	$rowsDetailLinkList = $sth->fetchAll();

}
catch(Exception $e) {
	$dbh = null;
	header('Location: http://thetop10.ru/');
	exit;
}
?>

<div class="listspacer"></div>
<?if(count($rowsDetailLinkList)>0):?>
<div class="link-near">
<h3>Стоит посмотреть</h3>
<ul class="linklist">
<?$i=0;
while($i<count($rowsDetailLinkList)):?>
<li><a href="http://thetop10.ru/<?=$rowsDetailLinkList[$i][url]?>/"><?=$rowsDetailLinkList[$i][name]?></a></li>
<?
$i++;
endwhile;?>
</ul>
</div>
<div class="listspacer"></div>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<?endif;?>
<div>
<!-- new123 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-3388378408783637"
     data-ad-slot="3089150709"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>

<script>
	$(document).ready(function () {

$(".commentlinks").hover(
      function () {
       $(this).find(':first-child').animate({"margin-top":"10px"}, 100);
      },
      function () {
      $(this).find(':first-child').animate({"margin-top":"33px"}, 100);
      }
    );
});

function plus (x) {

		$.ajax({
		  type: "POST",
		  url: "../../commentVote.php",
		  data: "ip=<?echo GetRealIp();?>&id="+x+"&action=+1",
                             success: function(msg){
                               if(msg == "1") {
                                if(isNaN(parseInt($("#openqq"+x).html()))){var currentRating = 1;}
                                else{var currentRating = parseInt($("#openqq"+x).html())+1;}
if(currentRating == 0){
 $("#openqq"+x).html("");
}
else{
$("#openqq"+x).html(currentRating);
}
                                }
                              }
		});
 $("#tup"+x).addClass( "nounderline" );
if(!$("#tup"+x).hasClass("opacity50")){
 $("#tdown"+x).addClass( "opacity50 nounderline" );
}
 
}


function minus (x) {

		$.ajax({
		  type: "POST",
		  url: "../../commentVote.php",
		  data: "ip=<?echo GetRealIp();?>&id="+x+"&action=-1",
                             success: function(msg){
                               if(msg == "1") {
                                if(isNaN(parseInt($("#openqq"+x).html()))){var currentRating = -1;$("#openqq"+x).addClass("negative");}
                                else{var currentRating =  parseInt($("#openqq"+x).html())-1;}
if(currentRating == 0){
 $("#openqq"+x).html("");
}
else{
$("#openqq"+x).html(currentRating);
}
                                }
                              }
		});
 $("#tdown"+x).addClass( "nounderline" );
if(!$("#tdown"+x).hasClass("opacity50")){
 $("#tup"+x).addClass( "opacity50 nounderline" );
}

}

</script>

<?

$sql = 'SELECT name, url FROM topik WHERE categoryID = ? AND id != ? AND id <  ? ORDER BY id DESC LIMIT 8';
$sth =  $dbh->prepare($sql);

$sth->bindParam(1,  $categoryID);
$sth->bindParam(2,  $rowsParentList[0]['id']);
$sth->bindParam(3,  $rowsParentList[0]['id']);

$sth->execute();

$categoryList = $sth->fetchAll();

if(count($categoryList) < 8){

$sql = 'SELECT name, url FROM topik WHERE categoryID = ? AND id != ? AND id >  ? ORDER BY id ASC LIMIT '.(8 - count($categoryList));
$sth =  $dbh->prepare($sql);

$sth->bindParam(1,  $categoryID);
$sth->bindParam(2,  $rowsParentList[0]['id']);
$sth->bindParam(3,  $rowsParentList[0]['id']);

$sth->execute();

$categoryList2 = $sth->fetchAll();

$categoryList = array_merge($categoryList, $categoryList2);
}


$h = 0;
while($h < 8) {
$nameList[]= $categoryList[$h]['name'];
$urlList[]= $categoryList[$h]['url'];
$h++;
}

$isSidebarDetail = 1;

include ("../../footer.php");?>