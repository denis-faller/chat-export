<?
session_start();

function russian_date($dateIn){
$date=explode("-", $dateIn);
switch ($date[1]){
case 1: $m='января'; break;
case 2: $m='февраля'; break;
case 3: $m='марта'; break;
case 4: $m='апреля'; break;
case 5: $m='мая'; break;
case 6: $m='июня'; break;
case 7: $m='июля'; break;
case 8: $m='августа'; break;
case 9: $m='сентября'; break;
case 10: $m='октября'; break;
case 11: $m='ноября'; break;
case 12: $m='декабря'; break;
}

if($date[2][0] == '0'){
$n = $date[2][1];
}
else{
$n = $date[2];
}
return $n.'&nbsp;'.$m.'&nbsp;'.$date[0];
}

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



$url = str_replace ("/", "", $_SERVER['REQUEST_URI']);


$url = explode("?", $url);

$url = $url[0];


$redirectCommentUrl = $url;

$keyPage = 1;

$url = trim($url);


try {
	$dbh = new PDO('mysql:host=localhost;dbname=ch81240_u6878947', 'ch81240_u6878947', 'xVsXJ0l3jv1', array(PDO::ATTR_PERSISTENT => true));
	$dbh->exec('SET NAMES utf8'); 

	$sql = 'SELECT id, name, content, categoryID, linkTopik, userID, preview, timeInsert, adv, timeUpdate, noindex FROM topik WHERE url = ?';
	$sth =  $dbh->prepare($sql);

	$sth->execute(array($url));

	$rows = $sth->fetchAll();

        $previewShare = $rows[0][preview];

        $timeInsert= $rows[0][timeInsert];

       $advLink= $rows[0][adv];
	   
	   $noindex = $rows[0][noindex];

$LastModified_unix =strtotime( $rows[0][timeUpdate ]); // время последнего изменения страницы
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



              if(isset($rows[0][userID])){
	$sql = 'SELECT id, name, provider, profile, url FROM users WHERE  id = ?';
	$sth =  $dbh->prepare($sql);

	$sth->execute(array($rows[0][userID]));

	$rowsUsers = $sth->fetchAll();
              }

	$idparent = $rows[0]["id"];
              $linkID = $rows[0]["linkTopik"];
	$title = $rows[0]["name"];

	
	$visibleTitle = $title;
	
	if($keyPage!=1){$title = $title.' - Страница '.$keyPage;}


        $desc = $rows[0]["content"];
	
	$contentTitle = "Не согласен с топом? Тогда обязательно проголосуй - твой голос может стать решающим, или <a  class = 'scrollto' onclick = \"$('#addnewitem a').click();\" href = '#addnewitem'>добавь свой вариант</a>, а можешь просто <a href =\"http://thetop10.ru/".$url."/create/\" >создать свою подборку</a>.";

	$sql = 'SELECT COUNT(id) as count FROM list WHERE idparent = ?';
	$sth =  $dbh->prepare($sql);

	$sth->execute(array($idparent));

	$countPageList = $sth->fetchAll();
	
	
	$countPage = ceil($countPageList[0][count]/100);



        $categoryID = $rows[0]["categoryID"];

        $sql = 'SELECT name, url, nameNav FROM category WHERE id = ?';
	$sth =  $dbh->prepare($sql);
	$sth->bindParam(1, $categoryID);
	$sth->execute();
	$categoryName = $sth->fetchAll();

	$sql = 'SELECT 1 FROM vote WHERE idparent = ? AND ip = ?';
	$sth = $dbh->prepare($sql);
	$sth->bindParam(1, $idparent);
	$sth->bindParam(2, GetRealIp());
	$sth->execute();
	$voteFlag = $sth->fetchAll();
	
	if(isset($voteFlag[0][1])) {
		$isVote = 1;
	}
	else {
		$isVote = 0;
	}
	
	$sessionId = false;
	if(isset($_SESSION["top"][$idparent])){
		$sessionId = true;
	}
	
$sql = 'SELECT idList, COUNT(idList) AS countList, name, urlList, href, ozon FROM vote INNER JOIN list ON list.id = vote.idList WHERE vote.idparent = ? GROUP BY idList ORDER BY countList DESC LIMIT '.(($keyPage-1)*100).',100';


	$sth = $dbh->prepare($sql);
	$sth->bindParam(1, $idparent);
	$sth->execute();
	$voteCollection = $sth->fetchAll();



	$sql = 'SELECT COUNT(idList) as count FROM vote WHERE idparent = ?';
	$sth =  $dbh->prepare($sql);

	$sth->bindParam(1, $idparent);
              $sth->execute();

	$countVoteList = $sth->fetchAll();


                   if(isset($voteCollection[0])) {
			$b = 0;
		while($b < count($voteCollection)) {

			$procents[$voteCollection[$b]["idList"]] = (($voteCollection[$b]["countList"]/$countVoteList[0][count])*100);
			$b++;
		}


$isNullList = 0;
if(count($procents)!=100){


$sql = 'SELECT id, name, urlList, href, ozon  FROM list WHERE idparent = ? AND id NOT IN(SELECT t1.idList FROM(SELECT idList, COUNT(idList) AS countList FROM vote  WHERE idparent = ? GROUP BY idList ORDER BY countList)t1) ORDER BY id ASC LIMIT '.(100-count($voteCollection));

$sth =  $dbh->prepare($sql);

$sth->bindParam(1, $idparent);

$sth->bindParam(2, $idparent);

$sth->execute();

$rows = $sth->fetchAll();





		$isNullList = 1;


		$u = 0;
		while ($u < count($rows)) {
			if (!in_array($rows[$u]["id"], array_keys($procents))) {
				$procents[$rows[$u]["id"]] = 0;
			}
			$u++;
		}
}




                            if($isNullList){
                            $maxIndexRows = count($rows);
                            }
                            else{
                            $maxIndexRows = 0;
                            } 
		$z = 0;
		while($z < count($voteCollection)){
		$rows[$maxIndexRows][id] = $voteCollection[$z][idList];
		$rows[$maxIndexRows][name] = $voteCollection[$z][name];
		$rows[$maxIndexRows][urlList] = $voteCollection[$z][urlList];
                $rows[$maxIndexRows][href] = $voteCollection[$z][href];
                $rows[$maxIndexRows][ozon] = $voteCollection[$z][ozon];

		$maxIndexRows++;
                            $z++;

		}


		arsort($procents, SORT_NUMERIC);


		header('Content-type: text/html; charset=utf-8');
		
		$t = 0;
		foreach($procents as $key=>$value) {
			$i = 0;
			while($i < count($rows)) {
				if($key == $rows[$i]["id"]) {

					$idTopik[] = $rows[$i]["id"];
					$nameTopik[] = $rows[$i]["name"];
                                        $urlTopikList[] = $rows[$i]["urlList"];
                                        $hrefTopikList[] = $rows[$i]["href"];
                                        $ozonTopikList[] = $rows[$i]["ozon"];
					
					$sql = 'SELECT users.url as userUrl, users.name as userName, comment.id as id, comment.comment as comment, comment.rating as rating FROM comment LEFT JOIN users ON comment.userID = users.id WHERE idparent = ? ORDER BY rating DESC LIMIT 4';
					$sth =  $dbh->prepare($sql);

					$sth->execute(array($rows[$i]["id"]));

					$rowsComment = $sth->fetchAll();
					
					$q = 0;
					if(isset($rowsComment[$q]["comment"])) {
						while($q < count($rowsComment)) {	
                                                                                                  $idCommentTopik[$t][] = $rowsComment[$q]["id"];					
							$commentTopik[$t][] = $rowsComment[$q]["comment"];
                                                                                                  $ratingComment[$t][] = $rowsComment[$q]["rating"];
                                                                                                  $rowsIdUser[$t][] = $rowsComment[$q]["userUrl"];
                                                                                                  $rowsNameUser[$t][] = $rowsComment[$q]["userName"];					
							$q++;
						}
					}
					$t++;
				}
			$i++;
			}
		}
	}
	else {

 if($keyPage != 1){
$sql = 'SELECT idList, COUNT(idList), name, urlList, href, ozon AS countList FROM vote INNER JOIN list ON list.id = vote.idList WHERE vote.idparent = ? GROUP BY idList';

$sth =  $dbh->prepare($sql);

$sth->bindParam(1, $idparent);

$sth->execute();

$voteAllCollection = $sth->fetchAll();


$diff = (($keyPage-1)*100 - count($voteAllCollection));


if($diff > 0){

$x = $diff;

$sql = 'SELECT id, name, urlList, href, ozon FROM list WHERE idparent = ? AND id NOT IN(SELECT t1.idList FROM(SELECT idList, COUNT(idList) AS countList FROM vote  WHERE idparent = ? GROUP BY idList ORDER BY countList)t1) ORDER BY list.id ASC LIMIT '.$x. ',100';

}
else{
$sql = 'SELECT id, name, urlList, href, ozon FROM list WHERE idparent = ? AND id NOT IN(SELECT t1.idList FROM(SELECT idList, COUNT(idList) AS countList FROM vote  WHERE idparent = ? GROUP BY idList ORDER BY countList)t1) ORDER BY id ASC LIMIT 100';
}

$sth =  $dbh->prepare($sql);

$sth->bindParam(1, $idparent);

$sth->bindParam(2, $idparent);

$sth->execute();

$rows = $sth->fetchAll();

}
else{


		$sql = 'SELECT id, name, urlList, href, ozon FROM list WHERE idparent = ? ORDER BY id LIMIT 100';

		$sth =  $dbh->prepare($sql);

		$sth->execute(array($idparent));

		$rows = $sth->fetchAll();
}

	
		$i = 0;
		while($i < count($rows)) {
			$idTopik[] = $rows[$i]["id"];
			$nameTopik[] = $rows[$i]["name"];
                        $urlTopikList[] = $rows[$i]["urlList"];
                        $hrefTopikList[] = $rows[$i]["href"];
                        $ozonTopikList[] = $rows[$i]["ozon"];
			
			$sql = 'SELECT users.url as userUrl, users.name as userName, comment.id as id, comment.comment as comment, comment.rating as rating FROM comment LEFT JOIN users ON comment.userID = users.id WHERE idparent = ? ORDER BY rating DESC LIMIT 4';
			$sth =  $dbh->prepare($sql);

			$sth->execute(array($rows[$i]["id"]));

			$rowsComment = $sth->fetchAll();

			$q = 0;
			if(isset($rowsComment[$q]["comment"])) {
			while($q < count($rowsComment)) {
                                                                      $idCommentTopik[$i][] = $rowsComment[$q]["id"];
					$commentTopik[$i][] = $rowsComment[$q]["comment"];
                                                                      $ratingComment[$i][] = $rowsComment[$q]["rating"];
                                                                      $rowsIdUser[$i][] = $rowsComment[$q]["userUrl"];
                                                                      $rowsNameUser[$i][] = $rowsComment[$q]["userName"];
					$q++;
				}
			}
			$i++;
		}
	}

	$sql = 'SELECT 1 FROM vote WHERE idparent = ?';
	$sth = $dbh->prepare($sql);
	$sth->bindParam(1, $idparent);
	$sth->execute();
	$voteOne = $sth->fetchAll();
	
	if(isset($voteOne[0][1])) {
		$isVoteOne = 1;
	}
	else {
		$isVoteOne = 0;
	}



	include ("../header.php");


}
catch(Exception $e) {
	$dbh = null;
	header('Location: http://thetop10.ru/');
	exit;
}



?>
	<style>
			#addlist input, #addlist textarea {width:98.48%;font-family:Arial, Helvetica, sans-serif;font-size:12px;padding:3px}
			#addlist #title{color:#3c87b1;font-size:32px;}
			#addlist .formlistitem{font-size:22px;color:#3c87b1;margin:0 0 20px 45px; width:85%;}
			.single-new{margin:0px !important;}
			.load{background-image: url('http://thetop10.ru/load.png');
			display: block;
			float: left;
			height: 31px;
			width: 258px !important;}
.social-buttons{
position:relative;
}
.social-buttons-div{
position:absolute;
width:600px;
}
#vkshare0{
float: left;
margin-left:25px;
}
.twitter-share-button{
margin-left:10px!important;
margin-bottom: 5px!important;
}
#___plusone_0{
margin-left:5px!important;
}
h1{
margin-top:55px;
}
.commentsig-mod{
margin-left: 20px;
margin-top: -10px;
}
.mod10 {
    width: 490px!important;
}
.negative{
color: #F2A30B!important;
}
a.offtext{
color:#919399;
}
.commenttwitter {
height: 33px;
width: 33px;
float: right;
background: url('http://thetop10.ru/set.png');
background-position: -117px -126px;
margin-right: 25px;
margin-top: 5px;
}
.commenttwitter:hover {
background: url('http://thetop10.ru/set.png');
background-position: -150px -126px;
}
.commentvk{
height: 32px;
width: 33px;
float: right;
background: url('http://thetop10.ru/set.png');
background-position: -115px -90px;
margin-right: 5px;
margin-top: 5px;
}
.commentvk:hover{
background: url('http://thetop10.ru/set.png');
background-position: -148px -90px;
}
.commentfb{
height: 29px;
width: 33px;
float: right;
background: url('http://thetop10.ru/set.png');
background-position: -115px -62px;
margin-right: 5px;
margin-top: 5px;
}
.commentfb:hover{
background: url('http://thetop10.ru/set.png');
background-position: -146px -62px;
}
 .img-theitem {
width:30%;
}
	</style>
<link rel="image_src" href="http://thetop10.ru/img/<?=$previewShare?>">
<div id="crumbs"><a class="bhome" href="http://thetop10.ru/">Главная</a>»<a href="http://thetop10.ru/vse-topy/">Все топы</a>»<a href="http://thetop10.ru/<?=$categoryName[0][url];?>/"><?=$categoryName[0][nameNav]?></a></div>

	<h1><?echo $visibleTitle;?></h1>
<?if(isset($rowsUsers[0][url])):?>
<p class="commentsig">
<a style="margin-left:5px;" href="http://thetop10.ru/user/<?=$rowsUsers[0][url]?>"><?echo $rowsUsers[0][name];?></a>
</p>
<?endif;?>
<?if($keyPage==2):?><?echo "Не согласен с топом? Тогда обязательно проголосуй, твой голос может стать решающим, или <a  class = 'scrollto' onclick = \"$('#addnewitem a').click();\" href = '#addnewitem'>добавь свой вариант</a>, а можешь просто <a href =\"http://thetop10.ru/".$url."/create/\" >создать свою подборку</a>.";?><?endif;?>
<?if($keyPage==1):?><?echo nl2br($contentTitle);?><br><br><?endif;?>

<div class = "block-adsense-1">
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

<br><br>
<div class = "social-buttons" style="margin-bottom:25px;">
<div style = "float:right;"><?echo "Топ опубликован: ";?> <?echo russian_date($timeInsert);?></div>
</div>

<?

$creatorIdTop =  $idparent;

 try {
               if($_SESSION['user']['uid']!='') {
               $sql = 'SELECT url  FROM personal WHERE userID = ? AND idTopik = ?';
	 $sth = $dbh->prepare($sql);
	 $sth->bindParam(1, $_SESSION['user']['uid']);
	 $sth->bindParam(2, $creatorIdTop);
	 $sth->execute();
               $myTop = $sth->fetchAll();
              }
         }
         catch(Exception $e) {
	  $dbh = null;
                header('Location: http://thetop10.ru/');
	  exit;
              }

?>

<?if(isset($myTop[0])):?>
<?$linkMyTop = "http://thetop10.ru/".$myTop[0]['url'];?>
<?else:?>
<?$linkMyTop = "http://thetop10.ru/".$url."/create/"?>
<?endif;?>

<h3>
<?if($keyPage==1):?><span class="wrap-h3"><?echo $visibleTitle?></span><?else:?><span class="wrap-h3"><?echo $title;?></span><?endif;?>
<i></i>
<div class="h3tab" >
<a class="offtext" href = "<?=$linkMyTop?>">Твой топ</a>
<div class="offright1"></div>
</div>
<div class="h3tab h3opera">
<div class="onleft1"></div>
<div class="ontext">Весь топ</div>
<div class="onright1"></div>
</div>
</h3>
	<?
	$j = 1;

if($keyPage==1){
   $visibleTopInex = 1;
}
else{
    $visibleTopInex = (($keyPage-1)*100+1);
}

$flagEndList = 1;
	while($j < (count($rows)+1)):
	$k = $j-1;
	?>


<?if($k == 10 && $keyPage == 1):?>
<?$flagEndList = 0;?>
<div class="listspacer"></div>
<div class = "block-adsense-1">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Баннер 25072016 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-3388378408783637"
     data-ad-slot="1426861508"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
 <?
         try {
               $sql = 'SELECT name, url  FROM topik WHERE linkTopik = ? AND id != ? LIMIT 5';
	 $sth = $dbh->prepare($sql);
	 $sth->bindParam(1, $linkID);
               $sth->bindParam(2, $idparent);
	 $sth->execute();
               $linkTopik = $sth->fetchAll();
         }
         catch(Exception $e) {
	  $dbh = null;
                header('Location: http://thetop10.ru/');
	  exit;
              }

if(count($linkTopik) != 0):?>
<div class="link-near">
<h3>Близкие топы</h3>
<ul class="linklist">
<?$y = 0;
while($y < count($linkTopik)):?>
<li><a href="http://thetop10.ru/<?=$linkTopik[$y][url];?>/"><?=$linkTopik[$y][name];?></a></li>
<?
$y++;
endwhile;?>
</ul>
</div>
<div class="listspacer"></div>
<?endif;?>
<?endif;?>


	<div itemtype="http://schema.org/Thing" itemscope="" itemprop="itemListElement" class="listitem">
		<?if(!$isVote && !$sessionId):?>
			<?if($isVoteOne):?>
<?if($procents[$idTopik[$k]]<1 && $procents[$idTopik[$k]]!=0 && number_format((float)($procents[$idTopik[$k]]), 1)!=0):?>
<?$newProcents[$k] = number_format((float)($procents[$idTopik[$k]]), 1);?>
<?else:?>
<?$newProcents[$k] = (int)($procents[$idTopik[$k]]);?>
<?endif;?>


			<span task="addvote" class="vote" style="display: block;"><div onclick = "youVote(<?=$idTopik[$k]?>)" class = "addvote" id="<?="v".$k?>"></div></span>
			<?else:?>
			<?$newProcents[$k] = 0;?>
			<span task="addvote" class="vote" style="display: block;"><div onclick = "youVote(<?=$idTopik[$k]?>)" class = "addvote" id="<?="v".$k?>"></div></span>
			<?endif;?>
		<?else:?>
		<span class="vote" style="display: none;" task="addvote"><div class='voted'><i><?if($procents[$idTopik[$k]]<1  && $procents[$idTopik[$k]]!=0):?><?if(number_format((float)($procents[$idTopik[$k]]), 1) == 1.0):?>1%<?else:?><?if(number_format((float)($procents[$idTopik[$k]]), 1) == 0.0):?>0%<?else:?><?echo  number_format((float)($procents[$idTopik[$k]]), 1);?>%<?endif;?><?endif;?><?else:?><?echo (int)($procents[$idTopik[$k]]);?>%<?endif;?></i></div></span>
		<?endif;?>
		<div class="img-theitem">
		<span class="screen2		<?if($k < 10 && $ozonTopikList[$k] != ""):?><?if(trim($hrefTopikList[$k]!="")):?>ozon<?endif;?><?endif;?>">
<?
$widthItem = "";
if($k < 10 && $ozonTopikList[$k] != "" && $hrefTopikList[$k]!=""):?>
<?
echo "<a rel = 'nofollow' target = '_blank' href = ".$hrefTopikList[$k].">".$ozonTopikList[$k].'</a>';
?>
<?
$widthSlider = "width:500px;";
?>
<?elseif($k < 10 && $ozonTopikList[$k] != ""):?>
<?
$widthSlider = "width:600px;";
?>
<?
echo $ozonTopikList[$k];
?>
<?
$widthSlider = "width:500px;";
?>
<?else:?>
<?
$widthItem = "noimg";
$widthSlider = "width:600px;";
?>
<?
$widthSlider = "width:500px;";
?>
<?endif;?>
<div id = "<?="e".$idTopik[$k]?>"></div>
</span>
		</div>

		<div class="theitem detail-item <?echo $widthItem;?>">
			<b><span class="single"><?if($j==1 && $keyPage==1):?><div class="crown"></div><?endif;?><?=$visibleTopInex;?></span><span itemprop="name"><?=$nameTopik[$k]?></span></b>              

                                         <div id = "<?="ee".$idTopik[$k]?>"></div>
			<span itemprop="description">
			<?
			if(isset($commentTopik[$k])):
			$l = 0;
			while($l < count($commentTopik[$k])):?>
				<div thumbs="<?=$ratingComment[$k][$l]?>" class="comment">
<span class="openq"><b id = "openqq<?=$idCommentTopik[$k][$l];?>" <?if($ratingComment[$k][$l]<0):?>class = "negative"<?endif;?>><?if($ratingComment[$k][$l]!=0):?><?echo $ratingComment[$k][$l];?><?endif?></b></span>
<?echo nl2br($commentTopik[$k][$l]);?>
<div class="commentlinks">
<div class="slider" style ="<?echo $widthSlider;?>display: block; margin-top: 33px;"></div>
<div class="slidercontent"  style =" display: block;"><span onclick = "plus(<?=$idCommentTopik[$k][$l];?>)"  class="tup" id ="tup<?=$idCommentTopik[$k][$l];?>">+1</span><span onclick = "minus(<?=$idCommentTopik[$k][$l];?>)"  class="tdown" id ="tdown<?=$idCommentTopik[$k][$l];?>">-1</span></div>
</div>
</div>

<?if(isset($rowsIdUser[$k][$l])):?>
<p class="commentsig commentsig-mod"><b><a href = "http://thetop10.ru/user/<?=$rowsIdUser[$k][$l];?>"><?echo $rowsNameUser[$k][$l];?></a></b></p>
<?endif;?>

			<?if($l == 2 && (count($commentTopik[$k])>3)):?>
			</span><a class="morecomments" href = "http://thetop10.ru/<?=$url.'/'.$urlTopikList[$k];?>">Посмотреть все комментарии</a>
                         <?$isStopComment = 1;?>
                       <?break;?>
			<?endif;?>
			<?$l++;
			endwhile;
			endif;?>
<?if($isStopComment == 1):?>
</span>
<?$isStopComment = 0;?>
<?endif;?>
<?if($hrefTopikList[$k]!=null  && $categoryID == 2):?>
<?endif;?>
<?if($k < 10 && ($hrefTopikList[$k]!=null)  && $keyPage == 1 && $categoryID == 11):?>
<div class="view"><em></em><a itemprop="url" target="_blank" href="http://thetop10.ru/go.php?url=<?=$hrefTopikList[$k];?>"><?=$nameTopik[$k];?></a></div>
<?endif?>
		</div><br clear="all">
	</div>
	<?$j++;
 $visibleTopInex++;
	endwhile;?>

<?if($flagEndList == 1 && $keyPage == 1):?>
<div class="listspacer"></div>
<div style = "margin-left:8px!important;">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Баннер 25072016 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-3388378408783637"
     data-ad-slot="1426861508"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
 <?
         try {
               $sql = 'SELECT name, url  FROM topik WHERE linkTopik = ? AND id != ? LIMIT 5';
	 $sth = $dbh->prepare($sql);
	 $sth->bindParam(1, $linkID);
               $sth->bindParam(2, $idparent);
	 $sth->execute();
               $linkTopik = $sth->fetchAll();
         }
         catch(Exception $e) {
	  $dbh = null;
                header('Location: http://thetop10.ru/');
	  exit;
              }

if(count($linkTopik) != 0):?>
<div class="link-near">
<h3>Близкие топы</h3>
<ul class="linklist">
<?$y = 0;
while($y < count($linkTopik)):?>
<li><a href="http://thetop10.ru/<?=$linkTopik[$y][url];?>/"><?=$linkTopik[$y][name];?></a></li>
<?
$y++;
endwhile;?>
</ul>
</div>
<div class="listspacer"></div>
<?endif;?>
<?endif;?>

	<br><br>
	<div id = "listcreate">
	<form id="addlist" onsubmit="return false;" action="http://thetop10.ru/load.php" method="POST" ENCTYPE="multipart/form-data">
		<input type="hidden" id="hidden" name="hidden" value="<?=$j?>">
		<input type="hidden" id="itemUrl" name="itemUrl" value="<?=$url?>">
		<div id = "end"></div>

<div id="pagelinks">


<?if($countPage!=1):?>
<div id="catlinks">
<?if($keyPage==1):?>
<div class="leftarrowactive"></div>
<?else:?>
<?if(($keyPage-1)==1):?>
<a class="leftarrowactive" href = "http://thetop10.ru/<?=$url;?>"></a>
<?else:?>
<a class="leftarrowactive" href = "http://thetop10.ru/<?=$url;?>-<?=$keyPage-1?>"></a>
<?endif;?>
<?endif;?>
<div id="scrollbox" style="float:left;overflow:hidden;height:31px"><div id="catlinksdiv">


	<?
              if($keyPage!=1){
              $l = $keyPage - 1;
              }
              else{
              $l = $keyPage;
              }
              $indexEnd = 1;
	while($l <= $countPage):?>
<??>
<?if($l == $keyPage):?>
<b id="catlink" class="catlink"><?echo $l;?></b><div class="catlinkspacer"></div>
<?else:?>
<?if($l == 1):?>
<a href = "<?="http://thetop10.ru/".$url;?>" class="catlink"><?echo $l;?></a><div class="catlinkspacer"></div>
<?else:?>
<a href = "<?="http://thetop10.ru/".$url."-".$l;?>" class="catlink"><?echo $l;?></a><div class="catlinkspacer"></div>
<?endif;?>
<?if($indexEnd == 3):?>
<?break;?>
<?endif;?>	              
<?endif;?>
	<?
	$l++;
              $indexEnd++;
	endwhile;?>

<div id="catend" style="float:left"></div>
</div></div>
<?if($keyPage==$countPage):?>
<div class="rightarrowactive"></div>
<?else:?>
<a class="rightarrowactive" href = "http://thetop10.ru/<?=$url;?>-<?=$keyPage+1?>"></a>
<?endif;?>
</div>
<?endif;?>

</div>


		<div id="additemswap">
		<div id="addnewitem" class="visitsponsorednew" name = "addnewitem"><a><em></em>Добавить</a></div>				
		</div>
	<button id="addbuttondummy" style="visibility:hidden;" class="greenbutton">ГОТОВО</button>
	<a id = "fe2" href = "#form-error" rel="leanModal" ></a>
	</form>
	</div>
		<form id="form-error" style="display: none; left: 497px; top: 328px; margin: 0px;" onsubmit="return false;" method = "post">
	<div class = "modal_close" id="closebutton"></div>
	<h4>Ошибка</h4><br><br><br>
	<b style = "margin-left:20px;">Просим заполнить все поля</b>
	</form>
	<br><br><br>
<br>
<!--
<div id="vk_comments"></div>
<script type="text/javascript">
VK.Widgets.Comments("vk_comments", {limit: 10, width: "520", attach: "*"}, '<?=$idparent;?>');
</script>
-->
<a id = "fe4" href = "#form-error-4" rel="leanModal" ></a>
		<form id="form-error-4" style="display: none; left: 497px; top: 328px; margin: 0px;" onsubmit="return false;" method = "post">
	<div class = "modal_close" id="closebutton"></div>
	<h4>На модерации</h4>
	<h1>Ваш отзыв находится на модерации</h1>
	<a href='https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fthetop10.ru%2F<?=$redirectCommentUrl;?>%2F&amp;text=<?=$title;?>&amp;tw_p=tweetbutton&amp;url=http%3A%2F%2Fthetop10.ru%2F<?=$redirectCommentUrl;?>' target='_blank' id='twitterlink'><div class='commenttwitter'></div></a><a href = 'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fthetop10.ru%2F<?=$redirectCommentUrl;?>%2F' target = '_blank'><div class='commentfb'></div></a><a href = 'http://vk.com/share.php?url=http%3A%2F%2Fthetop10.ru%2F<?=$redirectCommentUrl;?>%2F' target = '_blank'><div class='commentvk'></div></a>
	</form>
	<script>
	$(document).ready(function () {
			itemElement = 1;
			index = <?=$countPageList[0][count]+1;?>;
			$('#addnewitem a').click(function() {
				$("div#end").before("<span class='single single-new'>"+index+"</span><input type='text' class='formlistitem form-field' maxlength='255' id='title"+itemElement+"' name='title"+itemElement+"'><div class='itemspacer'></div><br><br>");
				itemElement += 1;
				index += 1;
				$('#addbuttondummy').css({'visibility': 'visible'});
		});

$(".commentlinks").hover(
      function () {
       $(this).find(':first-child').animate({"margin-top":"10px"}, 100);
      },
      function () {
      $(this).find(':first-child').animate({"margin-top":"33px"}, 100);
      }
    );

    $("a.scrollto").click(function () {
        var elementClick = $(this).attr("href");
        var destination = $(elementClick).offset().top;
        jQuery("html:not(:animated),body:not(:animated)").animate({scrollTop: destination}, 800);
        return false;
    });
		$('.addvote').click(function() {
			<?if(isset($newProcents)):?>
			var foo = new Array(<?	
			$o = 0;
			while($o < count($newProcents)):
				if($o != (count($newProcents)-1))
					echo $newProcents[$o].',';
				else
					echo $newProcents[$o];
				$o++;
			endwhile;
			?>, 0);
			m = 0;
			while (m < (foo.length - 1)) {
				$('#v'+m).before("<div class='voted'><i>"+foo[m]+"%</i></div>");
				m++;
			}
			$('.addvote').remove();
			<?endif;?>
		});
		$('#addbuttondummy').click(function() {
			i = 0;
			while(i < $('.form-field').length){
				val = $('.form-field:eq('+i+')').val();
					if($.trim(val) == "") {
						$('#fe2').click();
						break;
					}
				i++;
			}
			if(i == $('.form-field').length){
				$('#error').css({'visibility': 'hidden'});
				$("#addlist").attr("onsubmit", "return true;");
                                                            $('#additemswap').hide();
                                                            $('#addbuttondummy').hide();
			}
		});
		setTimeout(function () {
		$('.vote').css('display', 'block');
		}, 2000);
		
	});
	var BlockShare = 0

	function showShare(x) {
		if(BlockShare != 1){
                $('#f'+x).after("<div id='commentform' style='opacity: 1;'><div style='background: #F9F9F9;box-shadow: 0 0 5px #EAEAEA inset,0 0 10px #EAEAEA inset;<div id='s"+x+"' style='display:none'><div class='commentresponse'><div class='commentleftsm'></div><div class='commentrightsm'></div><a href='https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fthetop10.ru%2F<?=$redirectCommentUrl;?>%2F&amp;text=<?=$title;?>&amp;tw_p=tweetbutton&amp;url=http%3A%2F%2Fthetop10.ru%2F<?=$redirectCommentUrl;?>' target='_blank' id='twitterlink'><div class='commenttwitter'></div></a><a href = 'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fthetop10.ru%2F<?=$redirectCommentUrl;?>%2F' target = '_blank'><div class='commentfb'></div></a><a href = 'http://vk.com/share.php?url=http%3A%2F%2Fthetop10.ru%2F<?=$redirectCommentUrl;?>%2F' target = '_blank'><div class='commentvk'></div></a><b style='padding:9px 14px 9px 14px'>Ваш голос учтен! Поделиться  </b></div></div></div></div>");
                $('#f'+x).hide('slow',function(){$(this).remove()});
                $('#s'+x).show(1000);
				$('.addcomment').css({'visibility': 'visible'});
				BlockShare = 1;
		}
	}


	function valid(x){
                            var commenttext = $.trim($('textarea#ff'+x).val());
		if (commenttext.length < 10) {
		      $('.commenterror').css({'visibility': 'visible'});
		}
		else {
		
				$.ajax({
				  url:"http://thetop10.ru/comment.php",
				  type: "POST",
				  data: {contentComment:$('#ff'+x).val(), commentHide : $('#commentHide'+x).val()},
				  success: function(arr){
				}});
				$('#fe4').leanModal().click();
                $("#commentform").css({'display': 'none'});
		}
	}
	
	function youVote(x) {
		$.ajax({
		  type: "POST",
		  url: "../vote.php",
		  data: "ip=<?echo  GetRealIp();?>&idTopik=<?echo $idparent;?>&idList="+x
		});
                $('#ee'+x).before("<span itemprop='description' id='f"+x+"'><div id='commentform'><div style='display:block'><div class='commentmid'><b>Прокомментируйте свой выбор:</b><form  onsubmit='return false;' method = 'POST' id='thecommentform'><input type='hidden' id = 'commentHide"+x+"' name='commentHide' value='"+x+"'><input type='hidden' name='commentUrl' value='<?=$redirectCommentUrl;?>'><textarea id = 'ff"+x+"' style = 'resize: none;'  onkeyup='commentScore();' name='contentComment'></textarea><div id='commentbuttons' style='display: block;'><button class='postcomment greenbutton' onclick = 'valid("+x+")' >Отправить</button><button onclick='showShare("+x+")' class='nothanks whitebutton'>Я передумал</button></div><div class = 'commenterror'>Минимальная длина комментария - 10 символов</div></form></div></div></div></span>");
		$('.addcomment').css({'visibility': 'hidden'});
	}

function plus (x) {

		$.ajax({
		  type: "POST",
		  url: "../commentVote.php",
		  data: "ip=<?echo  GetRealIp();?>&id="+x+"&action=+1",
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
		  url: "../commentVote.php",
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

var prev_play_id = 0;

function listen (id) {
if($('#listening'+id).hasClass("start-play")){
if(prev_play_id!=0){
document.getElementById('player'+prev_play_id ).pause();
$('#listening'+prev_play_id).addClass("start-play");
$('#listening'+prev_play_id).removeClass("stop-play");
$('#listening'+prev_play_id).html("Послушать");
}
document.getElementById('player'+id).play();
$('#listening'+id).removeClass("start-play");
$('#listening'+id).addClass("stop-play");
$('#listening'+id).html("Пауза");
prev_play_id = id;
}
else{
document.getElementById('player'+id).pause();
$('#listening'+id).addClass("start-play");
$('#listening'+id).removeClass("stop-play");
$('#listening'+id).html("Послушать");
}
}
	</script>
	
<?

$sql = 'SELECT name, url FROM topik WHERE categoryID = ? AND id != ? AND id <  ? ORDER BY id DESC LIMIT 8';
$sth =  $dbh->prepare($sql);

$sth->bindParam(1,  $categoryID);
$sth->bindParam(2,  $idparent);
$sth->bindParam(3,  $idparent);

$sth->execute();

$categoryList = $sth->fetchAll();

if(count($categoryList) < 8){

$sql = 'SELECT name, url FROM topik WHERE categoryID = ? AND id != ? AND id >  ? ORDER BY id ASC LIMIT '.(8 - count($categoryList));
$sth =  $dbh->prepare($sql);

$sth->bindParam(1,  $categoryID);
$sth->bindParam(2,  $idparent);
$sth->bindParam(3,  $idparent);

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


$isTopikId = $idparent;

$isSidebarDetail = 1;

include ("../footer.php");?>