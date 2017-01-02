<?php
require_once('includes/include.php');
if(isset($_REQUEST['method'])) {
    User::loginOrRegister($_REQUEST);
}
if(isset($_REQUEST['logout'])) User::logout();
$loggedIn = $user = false;
if(isset($_SESSION['userId'])) {
    $user = User::getById($_SESSION['userId']);
}

$questions = [];
if (isset($_REQUEST['viewresult']) && is_object($user)) {
    $result = Result::getById($_REQUEST['viewresult']);
    if(!is_object($result) || $result->getUser() != $user->getId()) {
        header("Location: //".$_SERVER['HTTP_HOST']);
        exit();
    }
    $res = $result->getResult();
    foreach($res as $p=>$r) {
        $_POST[$p] = $r;
    }
    $_POST['mark'] = 1;
} elseif (isset($_REQUEST['results']) && is_object($user)) {
    $results = $user->getResults();
}
if (isset($_POST['mark'])) {
    unset($_POST['mark']);
    $score['total'] = $score['correct'] = $score['wrong'] = 0;
    foreach($_POST as $i=>$q) {
        $question = Question::getById($i);
        $answer = Answer::getById($q);
        if(!is_object($question) || !is_object($answer)) continue;
        if($answer->isCorrect()) {
            $score['correct']++;
        } else {
            $output .= "<span style='font-weight:bold'>".$question->getQuestion()."</span><br>";
            $corAnswer = $question->getCorrectAnswer();
            $output .= "Your Answer: ".$answer->getAnswer()."<br>Correct Answer: ".$corAnswer->getAnswer().'<br><br>';
            $score['wrong']++;
        }
        $score['total']++;
    }
    if(is_object($user)) {
        $user->storeResult($_POST, $score);
    }
} else {
    if(isset($_GET['questions'])) {
        define('QUESTION_COUNT', intval($_GET['questions']));
    } else {
        define('QUESTION_COUNT', 60);
    }
    $questions = Question::getQuestions(QUESTION_COUNT);
}
?><!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>NZART - Practice Exam</title>
        <meta name="description" content="Unofficial NZART Practice Exam">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="apple-touch-icon" href="apple-touch-icon.png">

        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.12.0.min.js"><\/script>')</script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
    </head>
    <body>
        <div id="container">
            <div id="user"><?php if(is_object($user)) {
                    echo "<a href='index.php?results=1'>Result History</a>. Welcome Back ".$_SESSION['username'].".<br><a href='index.php?logout=1'>Logout</a>";
                } else {
                    echo "<div id='loginTrigger'>Login or Register</div>";
                } ?></div>
            <div id="cover" style="display:none;"></div>
            <div id="login" style="display:none;">
                <h2 style="margin:0 0 5px 0;padding:0;">Login/Register</h2>
                <form method="post">
                    <label>Username<input type="text" name="username"><br/></label>
                    <label>Password<input type="password" name="password"><br/></label><br/>
                    <input type="hidden" name="method" value="register">
                    <button type="submit" class="loginbutton" value="login">Login</button>
                    <button type="submit" class="loginbutton" value="register">Register</button>
                    <script type="text/javascript">
                        $(".loginbutton").on("click", function(){
                            $('input[name=method]').attr("value",$(this).attr("value"));
                        });
                    </script>
                </form>
            </div>
            <div id="header"><h1>Unofficial NZART Practice Exam</h1></div>
            <div id="body" class="center">
                New Exam: <a href="/index.php?questions=10">10 Questions</a> -
                <a href="/index.php?questions=20">30 Questions (Half Exam)</a> -
                <a href="/index.php?questions=60">60 Questions (Full Exam)</a> -
                <a href="/index.php?questions=600">600 Questions (All Questions)</a>
                <br/><br/>
                <?php if(isset($score)) {
                        echo "<h3>Score ".(($score['correct']/$score['total'])*100)."% (".$score['correct']."/".$score['total'].")</h3>";
                        echo $output;
                    } elseif (isset($results)) {
                        $day = [];
                        foreach ($results as $i=>$result) {
                            $score = $result->getScore();
                            $date = date("Y-m-d", $result->getTime());
                            $day[$date][] = date("h:i a", $result->getTime())." - Score ".(($score['correct']/$score['total'])*100)."% (".$score['correct']."/".$score['total']."). ".
                            "<a href='index.php?viewresult=".$result->getId()."'>View Result</a><br/>";
                            if(!isset($day[$date]['total'])) {
                                $day[$date]['total'] = ($score['correct'] / $score['total']) * 100;
                            } else {
                                $original = $day[$date]['total'];
                                $day[$date]['total'] = ($original + (($score['correct'] / $score['total']) * 100)) / 2;
                            }
                        }

                        foreach ($day as $t=>$da) {
                            echo "<span style='font-weight:bold'>".$t."</span><br/>";
                            $total = $da['total'];
                            unset($da['total']);
                            foreach($da as $c=>$d) {
                                echo $d;
                            }
                            echo $total."% Average<br/><br/>";
                        }

                } else {
                    if(!is_object($user)) { ?>
                        Please Login to track results.<br>
                        <?php } ?><span style="font-weight:bold">
                <?=QUESTION_COUNT?> Questions</span><br><br>
                <form action="/" method="POST">
                <table>
                    <tbody>
                    <?php
                        $letters = range('A', 'Z');
                        foreach($questions as $i=>$q) {
                            echo "<tr>";
                                echo "<td>".($i+1)."</td>";
                                echo "<td style='padding-left:10px'>".$q->getQuestion()."</td>";
                            echo "</tr>";
                            if(!empty($q->getImage())) {
                                echo "<tr>";
                                    echo "<td></td><td><img src='img/".str_replace("\"",'',$q->getImage())."'></td>";
                                echo "</tr>";
                            }
                            foreach($q->answers as $x=>$ans) {
                                echo "<tr>";
                                    echo "<td></td>";
                                    echo "<td style='padding-left:10px'><input type='radio' name='".$q->getId()."' value='".$ans->getId()."' required> ".($letters[$x].". ".$ans->getAnswer())."</td>";
                                echo "</tr>";
                            }
                            echo "<tr><td>&nbsp;</td><td></td></tr>";
                        }
                    ?>
                    <tr><td></td><td><input type="hidden" name="mark" value="1"><button type="submit">Submit</button></td></tr>
                    </tbody>
                </table>
                </form>
                <?php } ?>
            </div>
            <div id="footer">
                All Questions and images from NZART question bank located
                <a target="_blank" href="http://www.nzart.org.nz/exam/download-examination-files/">Here</a>.<br/>
                This site is open-source. Code can be found <a target="_blank" href="https://gitlab.com/idanoo/nzart-exam">here</a>.<br>
                Last updated 31-12-2016.
            </div>
        </div>
    </body>
</html>
