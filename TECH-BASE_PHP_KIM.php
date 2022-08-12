<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>TECH-BASE_PHP_KIM</title>
</head>
<body>
    <?php
        //データベースと接続
        $dsn = 'データベース名';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        $createcomtb = "CREATE TABLE IF NOT EXISTS comtb"   //初回テーブル作成
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name char(32),"
        . "comment TEXT,"
        ."date TIMESTAMP"
        .");";
        $stmt = $pdo->query($createcomtb);

        $createpasstb = "CREATE TABLE IF NOT EXISTS passtb"   //初回テーブル作成
        ." ("
        ."id INT AUTO_INCREMENT PRIMARY KEY,"
        . "password TEXT NOT NULL"
        .");";
        $stmt = $pdo->query($createpasstb);

        $editname = "";
        $editcomment = "";
        $editnum = "";
        $editpw = "";

        if (!empty($_POST)&&$_POST["name"]!==""&&$_POST["comment"]!==""&&$_POST["edit_n"]=="") {
            if($_POST["password"]!==""){
                $name = $_POST["name"];
                $comment = $_POST["comment"];
                $password = $_POST["password"];
                $date = date("Y-m-d H:i:s");

                $sql = $pdo -> prepare("INSERT INTO comtb (name, comment, date) VALUES (:name, :comment, :date)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                $sql -> execute();

                $sql = $pdo -> prepare("INSERT INTO passtb (password) VALUES (:password)");
                $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                $sql -> execute();
            }
            elseif($_POST["password"]==""){
                echo "パスワードを入力してください<br>";
            }
        }

        //以下はコメントを削除するコード
        elseif(!empty($_POST)&&$_POST["delnum"]!==""){
            //パスワードが入力されていない場合
            if($_POST["delpw"]==""){
                echo "パスワードを入力してください。<br>";
            }
            elseif($_POST["delpw"]!==""){
                $delnum = $_POST["delnum"];
                $delpw = $_POST["delpw"];

                if(preg_match("[!-~]", $delnum) OR preg_match("[!-~]", $delpw)){
                    echo "フォームに記号を入力しないでください。<br>";
                }
                else

                $sql = 'SELECT * FROM passtb';
                $stmt = $pdo->query($sql);
                $results = $stmt->fetchAll();
                foreach ($results as $row){
                    //$rowの中にはテーブルのカラム名が入る
                    if($delnum == $row['id']){
                        if($delpw == $row['password']){
                            $id = $delnum;
                            $sql = 'delete from comtb where id=:id';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt->execute();

                            $sql = 'delete from passtb where id=:id';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                        else echo "パスワードが違います。<br>";
                    }
                }
            }
        }

        //以下はコメント編集を受け付けるコード
        elseif(!empty($_POST)&&$_POST["edit"]!==""){
            if($_POST["editpw"] == ""){
                echo "パスワードを入力してください<br>";
            }
            elseif($_POST["editpw"] !== ""){
                $editnum = $_POST["edit"];
                $editpw = $_POST["editpw"];
                $sql = 'SELECT * FROM passtb';
                $stmt = $pdo->query($sql);
                $p_results = $stmt->fetchAll();
                foreach ($p_results as $p_row){
                    //パスワードテーブルからパスワードを抽出
                    if($editnum == $p_row['id']){
                        if($editpw == $p_row['password']){
                            $id = $editnum;
                            //コメントテーブルからコメントを抽出
                            $sql = 'SELECT name, comment FROM comtb where id=:id';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt->execute();
                            $c_results = $stmt->fetchAll();
                            foreach ($c_results as $c_row){
                                $editname = $c_row['name'];
                                $editcomment = $c_row['comment'];
                            }
                        }
                        else echo "パスワードが違います。<br>";
                    }
                }
            }
        }

        elseif(!empty($_POST)&&$_POST["name"]!==""&&$_POST["comment"]!==""&&$_POST["edit_n"]!==""){
            $edit_n = $_POST["edit_n"];
            $edit_pw = $_POST["edit_pw"];
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $date = date("Y-m-d H:i:s");

            $sql = 'UPDATE comtb SET name=:name,comment=:comment,date=:date WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':id', $edit_n, PDO::PARAM_INT);
            $stmt->execute();
            
        }
    ?>

    <form action="" method="post">
        名前:<input type="comment" name="name" value="<?php echo $editname; ?>"><br>
        コメント:<input type="comment" name="comment" value="<?php echo $editcomment; ?>">
        パスワード:<input type="comment" name="password"><br>
        <input type="submit" name="submit"><br>
        <br>

        削除:<input type="comment" name="delnum" placeholder="削除したい投稿の数字を入力してください">
        パスワード:<input type="comment" name="delpw"><br>
        <input type="submit" name="submit"><br>
        <br>

        編集:<input type="comment" name="edit" placeholder="編集したい投稿の数字を入力してください">
        パスワード:<input type="comment" name="editpw"><br>
        <input type="submit" name="submit"><br>
        <input type="hidden" name="edit_n" value="<?php echo $editnum;?>">
        <input type="hidden" name="edit_pw" value="<?php echo $editpw;?>">
        <br>
    </form>

    <?php
        $sql = 'SELECT * FROM comtb';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            echo $row['id'].',';
            echo $row['name'].',';
            echo $row['comment'].',';
            echo $row['date'].'<br>';
        echo "<hr>";
        }
    ?>