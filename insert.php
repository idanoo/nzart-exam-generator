<?php
require_once("includes/include.php");
$questionArray = $masterLineArray = $questions = [];
$skip = 0;
$x = 1; //QUESTIONS.
for ($j = 1; $j <= count(glob("files/*")); $j++) {
    $fileOpen = file_get_contents("files/N".$j.".TXT");
    $jq=0;
    $fileArr = explode("\n", $fileOpen);
    while (!empty(trim($fileArr[$jq]))) { //trim notes at top.
        unset($fileArr[$jq]);
        $jq++;
    }
    $masterLineArray = array_merge($masterLineArray, $fileArr);
}
unset($fileOpen);
unset($j);
unset($jq);
$lines = array_map('trim', $masterLineArray);
$stillNeedQuestion = false;
//ANALYSE EACH LINE
foreach($lines as $i=>$line) {
//    if($i == 100) exit();
    if($skip != 0) {
        $skip--;
        continue;
    }

    if ($i == 3316) {
        $break = true;
    }
    if (strpos(strtolower($line), '%') !== false) {
        if (strpos(strtolower($line), 'ans ') !== false) {
            preg_match_all('/\d+/', substr($line,1), $matches);
            $questions[$x]['correctAnswer'] = $matches[0][0]; // <- this looks like eyes.
            $x++; //NEXT QUESTION.
        }
        continue;
    }
    if (strpos(strtolower($line), '<img') !== false) {
        $beforeStuff = explode("<", $line);
        if(!empty($beforeStuff[0])) {
            $questions[$x]['questionNumber'] = substr($beforeStuff[0], 1);
            $stillNeedQuestion = true;
        }
        preg_match('/".*?"/', $line, $matches);
        $questions[$x]['image'] = strtoupper($matches[0]);
        continue;
    }
    if(substr($line, 0, 1) == "#" || strpos(strtolower($line), 'totallines') !== false || strpos(strtolower($line), 'extralines') !== false) {
        if (strpos(strtolower($line), 'totallines') !== false || strpos(strtolower($line), 'extralines') !== false) {
            if (substr($line, 0,1) == "<" && substr( $line,-1) == ">") continue;
        } else {
            $numbers = explode(" ", $line);
            $questions[$x]['questionNumber'] = substr($numbers[0], 1);
        }
        $questionLine = $line;
        $q=$i;
        while(!empty($lines[$q+1])) {
            if(strpos(strtolower($lines[$q+1]), '<img') !== false) break;
            $questionLine .= $lines[$q+1];
            $q++;
            $skip++;
        }
        $questions[$x]['question'] = str_replace("#".$questions[$x]['questionNumber'].' ','',$questionLine);
    } else {
        if (empty($line)) continue;
        $q=$i;
        if($stillNeedQuestion) {
            $questions[$x]['question'] = $line;
            $q=$i;
            while(!empty($lines[$q+1])) {
                if(strpos(strtolower($lines[$q+1]), '<img') !== false) break;
                $questionLine .= $lines[$q+1];
                $q++;
                $skip++;
            }
            $stillNeedQuestion = false;
            continue;
        }
        $answerLine = $line;
        while(!empty($lines[$q+1])) {
            $answerLine .= $lines[$q+1];
            $q++;
            $skip++;
        }
        $questions[$x]['answers'][] = $answerLine;
    }
}

//CHECK ALL THE STUFF IS CORRECT.
$badCount = $totalCount = $goodCount = 0;
foreach($questions as $q) {
    $bad = false;
    if(!$q['correctAnswer']) $bad = true;
    if(!$q['questionNumber']) $bad = true;
    if(empty(trim($q['question']))) $bad = true;
    if (strpos(strtolower($q['question']), 'img') !== false) $bad = true;
    if (strpos(strtolower($q['question']), 'totallines') !== false) $bad = true;
    if (strpos(strtolower($q['question']), 'extralines') !== false) $bad = true;
    if(count($q['answers']) != 4) $bad = true;
    foreach($q['answers'] as $a) {
        if(empty(trim($a))) $bad = true;
    }
    if($bad) {
        var_dump($q); //DEBUGGING
        $badCount++;
    } else {
        $goodCount++;
    }
    $totalCount++;
//    var_dump($questions);
}

echo "Array count:".count($questionArray)."<br>Total questions:".$totalCount."<br>Good questions:".$goodCount."<br>Bad questions:".$badCount;
exit();

    //SECTIONS. Q/A/Q/A/Q/A
    foreach ($data as $i => $d) {
        if (empty($d)) continue;
        if (strpos(strtolower($d), 'question') !== false) {
            //PARSE THE QUESTION
            $fullQuestion = substr(preg_replace('/^.+\n/', '', $d), 1); //Strip useless firstline.
            $numbers = explode(" ", $fullQuestion);
            $questions[$i]['questionNumber'] = $numbers[0]; // Get # of question
            $lengthOfNumber = strlen($questions[$i]['questionNumber']); //Get length of #
            $splitNewLine = explode("\n", trim($fullQuestion)); //Split rest into lines.
            $twoLineQuestion = $imageFirstLine = $threeLineQuestion = false;
            if (strpos(strtolower($splitNewLine[0]), '<img') !== false) {
                preg_match('/".*?"/', $splitNewLine[0], $matches);
                $questions[$i]['image'] = strtoupper($matches[0]);

                if (strpos($splitNewLine[1], ':') !== false || !empty(trim($splitNewLine[2]))) {
                    $twoLineQuestion = true;
                }
                $cutOutImageShit = explode(">", $splitNewLine[1]);
                $trashBin = array_shift($cutOutImageShit);
                $firstLine = implode(" ", $cutOutImageShit);
                $questions[$i]['question'] = trim(substr(($twoLineQuestion ? $firstLine . " "
                    . $splitNewLine[2] : $firstLine), $lengthOfNumber));
            } else {
                if (strpos($splitNewLine[0], ':') !== false || !empty(trim($splitNewLine[1]))) {
                    if (strpos($splitNewLine[0], ':') !== false) {
                        if($questions[$i]['questionNumber'] == "10.2") error_log('here');

                        $twoLineQuestion = true;
                        if (strpos(strtolower($splitNewLine[1]), '<img') !== false) {
                            $twoLineQuestion = false;
                            preg_match('/".*?"/', $splitNewLine[1], $matches);
                            $questions[$i]['image'] = strtoupper($matches[0]);
                        }
                    } elseif (!empty(trim($splitNewLine[2]))) {

                        $threeLineQuestion = true;
                    }
                }
                $questions[$i]['question'] = trim(substr(($twoLineQuestion ? $splitNewLine[0] . " "
                    . $splitNewLine[1] : $splitNewLine[0]), $lengthOfNumber));
            }
            $x = $b = 1;
            $skip = false;
            foreach ($splitNewLine as $a => $line) {
                //Don't ask what this witchcraft is. I don't know either.
                if ($a == 0 || $skip) {
                    $skip = false;
                    continue;
                }
                if (($twoLineQuestion || $imageFirstLine) && $a == 1) continue;
                if ($threeLineQuestion || ($twoLineQuestion && $imageFirstLine) && $a == 2) continue;
                if ($imageFirstLine && $threeLineQuestion && $a == 3) continue;
                if (empty(trim($line))) continue;
                if (strpos(strtolower($line), 'totallines') !== false) continue;

                if (strpos(strtolower($line), '<img') !== false) {
                    preg_match('/".*?"/', $line, $matches);
                    $questions[$i]['image'] = strtoupper($matches[0]);
                    continue;
                }

//                if(!empty($splitNewLine[$a+1])) {
//                    if(!empty($splitNewLine[$a+2])) {
//                        $questions[$i]['answers'][$b] = trim($line) . trim($splitNewLine[$a + 1]) . trim($splitNewLine[$a + 2]);
//                    } else {
//                        $questions[$i]['answers'][$b] = trim($line) . trim($splitNewLine[$a + 1]);
//                    }
//                    $skip = true;
//                } else {
//                    $questions[$i]['answers'][$b] = trim($line);
//                }

                $questions[$i]['answers'][$b] = trim($line);

                $b++;
            }
        } elseif (strpos(strtolower($d), 'ans ') !== false) {
            //PARSE THE CORRECT ANSWER
            $questions[$i - 1]['correctAnswer'] = substr($d, 3);
        }
    }

//CHECK ALL THE STUFF IS CORRECT.
foreach($questions as $q) {
    $bad = false;
    if(count($q['answers']) != 4) {
        $bad = true;
    }
    if(!$q['correctAnswer']) $bad = true;
    if(!$q['questionNumber']) $bad = true;
    if(empty(trim($q['question']))) $bad = true;
    foreach($q['answers'] as $a) {
        if(empty(trim($a))) $bad = true;
    }
    if($bad) {
        var_dump($q);
        $badCount++;
    } else {
        $goodCount++;
    }
    $totalCount++;
}

    $questionArray = array_merge($questionArray, $questions);


echo "Array count:".count($questionArray)."<br>Total questions:".$totalCount."<br>Good questions:".$goodCount."<br>Bad questions:".$badCount;
exit();
$i = $b = $d = $a = $count = 0; //Clear the useless stuff from above.
foreach ($questionArray as $q) {
    $db = new db();
    $db->query("INSERT INTO question(question_time, questiondata_number, questiondata_content, questiondata_image)
                  VALUES(:qTime, :qNumber, :qContent, :qImage)");
    $db->bind("qTime", time());
    $db->bind("qNumber", $q['questionNumber'] ?: 0);
    $db->bind("qContent", $q['question']);
    $db->bind("qImage", $q['image'] ?: "");
    $db->execute();
    $lastRow = $db->lastInsertId();
    $db->kill(); //IS THIS EVEN NEEDED?
    $row = 1;
    foreach ($q['answers'] as $a) {
        $db = new db();
        $db->query("INSERT INTO answer(answer_time, answerdata_content, answerdata_question, answerdata_correct)
                      VALUES(:aTime, :aContent, :aQuestion, :aCorrect)");
        $db->bind("aTime", time());
        $db->bind("aContent", $a);
        $db->bind("aQuestion", $lastRow);
        $db->bind("aCorrect", ($q['correctAnswer'] == $row ? "1" : "0"));
        $db->execute();
        $db->kill();
        $row++;
    }
    $db = null;
    $count++;
}
echo "Inserted " . $count . " questions.";