<?php
die("remove this to prevent people hitting it live");
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
        if(strlen($matches[0])) $questions[$x]['image'] = strtoupper($matches[0]);
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
$badCount = $totalCount = $goodCount = $image = 0;
foreach($questions as $q) {
    $bad = false;
    if(!$q['correctAnswer']) $bad = true;
    if(!$q['questionNumber']) $bad = true;
    if(empty(trim($q['question']))) $bad = true;
    if (strpos(strtolower($q['question']), 'img') !== false) $bad = true;
    if (strpos(strtolower($q['question']), 'totallines') !== false) $bad = true;
    if (strpos(strtolower($q['question']), 'extralines') !== false) $bad = true;
    if(count($q['answers']) != 4) $bad = true;
    if($q['image']) $image++;
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

echo "Images:".$image."<br>Total questions:".$totalCount."<br>Good questions:".$goodCount."<br>Bad questions:".$badCount;
if($badCount!=0) exit();
$count = $answer = 0;
foreach ($questions as $q) {
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
        $answer++;
    }
    $db = null;
    $count++;
}
echo "Inserted " . $count . " questions and ".$answer." answers.";