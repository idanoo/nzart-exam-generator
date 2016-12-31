<?php
require_once("includes/include.php");
$questionArray = [];
$badCount = $totalCount = $goodCount = 0;
$files = glob("files/*");
foreach($files as $file) {
    $questions = [];
    $fileOpen = file_get_contents($file);
    $fileOpen = implode("\n", array_slice(explode("\n", $fileOpen), 6));
    $fileOpen = substr($fileOpen, 1);
    $data = explode("%", $fileOpen);
    $data = array_map('trim', $data);

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

}
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