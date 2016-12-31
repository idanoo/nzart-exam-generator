<?php
require_once('includes/include.php');
define('QUESTION_COUNT', 60); //How many questions to ask.
$questions = Question::getQuestions(QUESTION_COUNT);
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
                60 Questions
                <table>
                    <tbody>
                    <?php
                        $letters = range('A', 'Z');
                        foreach($questions as $i=>$q) {
                            echo "<tr>";
                                echo "<td>".($i+1)."</td>";
                                echo "<td>".$q->getQuestion()."</td>";
                            echo "</tr>";
                            if(!empty($q->getImage())) {
                                echo "<tr>";
                                    echo "<td></td><td><img src='img/".str_replace("\"",'',$q->getImage())."'></td>";
                                echo "</tr>";
                            }
                            foreach($q->answers as $x=>$ans) {
                                echo "<tr>";
                                    echo "<td style='padding-left:10px'>".$letters[$x]."</td>";
                                    echo "<td style='padding-left:10px'>".$ans->getAnswer()."</td>";
                                echo "</tr>";
                            }
                            echo "<tr><td>&nbsp;</td><td></td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
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
