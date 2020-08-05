<?php
// DB接続設定
$dsn = 'データベース名';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

//送信コマンドが要求された時の処理
if(isset($_POST["submit"])){
    //編集時の処理
    //空欄時以外に処理
    if(!empty($_POST["number"]) && !empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["password"])){
        //日付設定を行う
        $date = date("Y/m/d/ H:i:s");
        //SQL:select文　投稿番号を抽出
        $sql = 'SELECT * FROM mission39 WHERE num = :number';
        $stmt = $pdo->prepare($sql);
        $stmt -> bindParam(':number',$_POST["number"], PDO::PARAM_STR);
        //値の確定
        $stmt->execute();
        //selectしたレコード列を二重の配列として抽出
        $selectedrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        //配列が存在する時の処理
        if(!empty($selectedrows[0])){
            //編集するレコード列
            $editrow = $selectedrows[0];
            //編集番号のパスワード
            $editpass=$editrow['password'];
            //パスワード設定
            $password=$_POST["password"];
            //パスワードが正しい時のみの処理
            if($editpass == $password){  
                //SQL:update文　
                $sql = 'UPDATE mission39 SET num=:number, name=:name,comment=:comment, password=:password, today=:today WHERE num=:number';
                $stmt = $pdo->prepare($sql);
                $stmt -> bindParam(':number', $_POST["number"], PDO::PARAM_STR);
                $stmt -> bindParam(':name', $_POST["name"], PDO::PARAM_STR);
                $stmt -> bindParam(':comment', $_POST["comment"], PDO::PARAM_STR);
                $stmt -> bindParam(':password', $_POST["password"], PDO::PARAM_STR);
                $stmt -> bindParam(':today', $date, PDO::PARAM_STR);
                //バインドの確定
                $stmt->execute();
            
                //完了のメッセージ
                echo "投稿番号が".$_POST["number"]."に該当する行をアップデートしました。" ; 
            }else{
                echo "パスワードが間違っています。";
            }
        }
       
    }
    
    //新規投稿時の処理    
    //空欄時以外に処理
    elseif(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["password"])){
        //SQL:select文  投稿番号の最大値を抽出
        $sql = 'SELECT * FROM mission39 WHERE num=(SELECT Max(num) FROM mission39)';
        $stmt = $pdo->query($sql);
        //selectしたレコード列を二重の配列として抽出
        $selectedrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //最後のレコード列が空欄でない時のみ行う
        if(!empty($selectedrows[0])){
            $lastrow = $selectedrows[0];
            $lastnum = $lastrow['num'];
            $num = $lastnum+1;
        }else{
            $num = 1;
        }
        //日付設定を行う
        $date = date("Y/m/d/ H:i:s");
        //SQL:insert文 とりあえずレコード一列分の文を用意
        $sql = $pdo -> prepare("INSERT INTO mission39 (num, name, comment, password, today  ) VALUES (:num, :name, :comment, :password, :today)");
        //bindParam()関数で値を入力　execute()関数を用した際に値が確定する
        $sql -> bindParam(':num', $num, PDO::PARAM_STR);
	    $sql -> bindParam(':name', $_POST["name"], PDO::PARAM_STR);
        $sql -> bindParam(':comment', $_POST["comment"], PDO::PARAM_STR);
	    $sql -> bindParam(':password', $_POST["password"], PDO::PARAM_STR);
	    $sql -> bindParam(':today', $date, PDO::PARAM_STR);
        //値の確定
        $sql -> execute();
        //完了のメッセージ
        echo "コメントをデータベースに格納しました。";

 
    }else{
        //エラーメッセージ
        echo "入力してください";
    }
}
//削除コマンドが要求された時の処理
if(isset($_POST["delete"])){
    //空欄時以外の処理
    if(!empty($_POST["deletenum"]) && !empty($_POST["deletepassword"])){
        
        //パスワードの確認
        //SQL：select文　削除番号のレコード列を取得
	    $sql = 'SELECT * FROM mission39 WHERE num=:deletenum';
	    $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':deletenum', $_POST["deletenum"], PDO::PARAM_INT);
        //バインドの確定
        $stmt->execute();
        //取得したレコード列を二重配列にする
        $selectedrows= $stmt->fetchAll();
        
        //削除列が存在する時
        if(!empty($selectedrows[0])){
            //削除するレコード列
            $deleterow = $selectedrows[0];
            //削除するレコード列のパスワード
            $deletepass = $deleterow['password'];      
          
            //パスワードが正しかった時、DELETEを行う。
            if($deletepass==$_POST["deletepassword"]){
                
                //SQL：DELETE文  
                $sql = 'DELETE FROM mission39 WHERE num=:deletenum';
                $stmt = $pdo->prepare($sql);
                $params = array(':deletenum'=> $_POST["deletenum"]);

                //バインドの確定
                $stmt->execute($params);
    
                
    
              //完了のメッセージ
              echo "投稿番号が".$_POST["deletenum"]."に該当する行を消去しました。" ;
            }else{
              echo "パスワードを間違えています。";
            }
        }else{
            echo "該当する投稿番号はありません。";
        }     
    }else{
        echo "削除番号とパスワード両方を入力してください。";
    }
}
//編集コマンドが要求された時の処理
if(isset($_POST["edit"])){
    //空欄時以外の処理
    if(!empty($_POST["editnum"]) && !empty($_POST["editpassword"])){
        
        //SQL：select文 編集番号のレコード列を取得
	    $sql = 'SELECT * FROM mission39 WHERE num=:editnum';
	    $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':editnum', $_POST["editnum"], PDO::PARAM_INT);
        //バインドの確定
        $stmt->execute();
        
        //編集番号のレコード列を配列にする
        $selectedrows= $stmt->fetchAll();
        //編集番号が存在する時のみの処理
        if(!empty($selectedrows[0])){
            //編集番号のレコード列を取得
            $editrow = $selectedrows[0];
            //編集番号のパスワードを取得
            $editpass = $editrow['password'];

            //パスワードが正しい時のみの処理
            if($editpass==$_POST["editpassword"]){
                $setnumber = $editrow['num'];
                $setname = $editrow['name'];
                $setcomment = $editrow['comment'];
                $setpassword = $editrow['password'];
            }else{
            echo "パスワードを間違えています。";
            }
        }else{
            echo "該当する投稿番号はありません。";
        } 
    }else{
        echo "編集番号とパスワード両方を入力してください。";
    }
}
?>


<!--htmlパート-->
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF=8">
        <title>mission_5-1</title>
    </head>
    <body>
        <form action="" method="post">
        <!--入力欄-->
        投稿番号:<input type="text" name="number" placeholder="投稿番号" value="<?php if(isset($setnumber)){echo $setnumber;} ?>">
        名前:<input type="text" name="name" placeholder="名前" value="<?php if(isset($setname)){echo $setname;} ?>">
        コメント:<input type="text" name="comment" placeholder="コメント" value="<?php if(isset($setcomment)){echo $setcomment;} ?>">
        パスワード：<input type="text" name="password" placeholder="パスワードを設定" value="<?php if(isset($setpassward)){echo $setpassword;} ?>">
        <!--送信ボタン-->
        <input type="submit" name="submit">
        <br>
    </form>
    <form action="" method="post">
        <!--削除番号-->
        削除番号:<input type="text" name="deletenum" placeholder="削除番号を入力">
        パスワード:<input type="text" name="deletepassword" placeholder="パスワードを入力">
        <!--削除ボタン-->
        <input type="submit" name="delete" value="削除">
        <br><br>
    </form>
    <form action="" method="post">
      <!--編集番号-->
      編集番号:<input type="text" name="editnum" placeholder="編集番号を入力">
      パスワード:<input type="text" name="editpassword" placeholder="パスワードを入力">
      <!--編集ボタン-->
      <input type="submit" name="edit" value="編集">
    </form>
    </body>    
</html>       

<?php
// DB接続設定
$dsn = 'データベース名';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));


//SQL：select文
  //SELECT　列名1, 列名2　FROM　テーブル名 ←列名＝*は前列検索　*...実際の開発では使用を極力避ける
  $sql = 'SELECT num, name, comment, password, today FROM mission39';
  $stmt = $pdo->query($sql);
  $results = $stmt->fetchAll();
  foreach ($results as $row){
  //$rowの中にはテーブルのカラム名が入る
  echo "<article><div class=\"info\"><h2>".$row['num'].':'.$row['name']."</h2><time>".$row['today']."</time></div><p>".$row['comment']."</p></article>";
}
echo "<hr>";
?>

