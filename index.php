<?php
require_once('includes/include.php');
$questions = [];
if(!isset($_POST['mark'])) {
    if(isset($_GET['questions'])) {
        define('QUESTION_COUNT', intval($_GET['questions']));
    } else {
        define('QUESTION_COUNT', 60);
    }
    $questions = Question::getQuestions(QUESTION_COUNT);
    $total = false;
} else {
    unset($_POST['mark']);
    $total = count($_POST);
    $correct = $wrong = 0;
    $output = "";
    foreach($_POST as $i=>$q) {
        $question = Question::getById($i);
        $answer = Answer::getById($q);
        if($answer->isCorrect()) {
            $correct++;
        } else {
            $output .= "<span style='font-weight:bold'>".$question->getQuestion()."</span><br>";
            $corAnswer = $question->getCorrectAnswer();
            $output .= "Your Answer: ".$answer->getAnswer()."<br>Correct Answer: ".$corAnswer->getAnswer().'<br><br>';
            $wrong++;
        }
    }
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
    </head>
    <body>
        <div id="container">
            <div id="header"><h1>Unofficial NZART Practice Exam</h1></div>
            <div id="body" class="center">
                New Exam: <a href="/index.php?questions=10">10 Questions</a> -
                <a href="/index.php?questions=20">30 Questions (Half Exam)</a> -
                <a href="/index.php?questions=60">60 Questions (Full Exam)</a> -
                <a href="/index.php?questions=600">600 Questions (All Questions)</a>
                <br/><br/>
                <?php if($total) {
                    ?><?php
                        echo "<h3>Score ".(($correct/$total)*100)."% (".$correct."/".$total.")</h3>";
                        echo $output;
                    } else { ?>
                <?=QUESTION_COUNT?> Questions<br><br>
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
                <a href="http://www.nzart.org.nz/exam/download-examination-files/">Here</a><br/>
                Last updated 31-12-2016.
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.12.0.min.js"><\/script>')</script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
    </body>
</html>
