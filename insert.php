<?php
require_once("includes/include.php");
if (isset($_REQUEST['raw'])) {
    $questions = [];
    $data = explode("%", $_REQUEST['raw']);
    $data = array_map('trim', $data);
    foreach($data as $i=>$d) {
        if(empty($d)) continue;
        if (strpos($d, 'Question') !== false) {
            $fullQuestion = substr(preg_replace('/^.+\n/', '', $d),1); //Strip useless firstline.
            $numbers = explode(" ", $fullQuestion);
            $questions[$i]['questionNumber'] = $numbers[0]; // Get # of question

            $lengthOfNumber = strlen($questions[$i]['questionNumber']); //Get length of #
            $splitNewLine = explode("\n", trim($fullQuestion)); //Split rest into lines.
            $questions[$i]['question'] = trim(substr($splitNewLine[0], $lengthOfNumber));
            $x=$b=1;
            foreach ($splitNewLine as $a=>$line) {
                if($a==0) continue;
                if(empty(trim($line))) continue;
                if(strpos($line, 'totallines') !== false) continue;
                if(strpos($line, '<img') !== false) {
                    preg_match('/".*?"/', $line, $matches);
                    $questions[$i]['image'] = strtoupper($matches[0]);
                    continue;
                }
                $questions[$i]['answers'][$b] = trim($line);
                $b++;
            }
        } elseif (strpos($d, 'ans ') !== false) {
            $questions[$i-1]['correctAnswer'] = substr($d, 3);
        }
    }

    $i = $b = $d = $a = 0; //Clear the useless stuff from above.
    if(isset($_REQUEST['insert']) && $_REQUEST['insert'] == 1 && count($questions)) {
        $count = 0;
        foreach($questions as $q) {
            $db = new db();
            $db->query("INSERT INTO question(question_time, questiondata_number, questiondata_content, questiondata_image)
                          VALUES(:qTime, :qNumber, :qContent, :qImage)");
            $db->bind("qTime",time());
            $db->bind("qNumber", $q['questionNumber']?:0);
            $db->bind("qContent",$q['question']);
            $db->bind("qImage",$q['image']?:"");
            $db->execute();
            $lastRow = $db->lastInsertId();
            $db->kill(); //IS THIS EVEN NEEDED?
            $row = 1;
            foreach($q['answers'] as $a) {
                $db = new db();
                $db->query("INSERT INTO answer(answer_time, answerdata_content, answerdata_question, answerdata_correct)
                              VALUES(:aTime, :aContent, :aQuestion, :aCorrect)");
                $db->bind("aTime", time());
                $db->bind("aContent", $a);
                $db->bind("aQuestion", $lastRow);
                $db->bind("aCorrect",($q['correctAnswer']==$row?"1":"0"));
                $db->execute();
                $db->kill();
                $row++;
            }
            $db = null;
        }

        echo "Inserted ".$count." questions.";
    } else {
        var_dump($questions);
    }
}?>
<h1>Question inserter thingy.</h1>
<h3>RAW DATA</h3>
<form action="/insert.php" method="POST">
    Raw Data: <label>
        Actually insert: <input type="checkbox" name="insert" value="1">
        <textarea cols="200" rows="20" name="raw" required><?=$_REQUEST['raw']?:""?></textarea>
    </label><br/><br/>
    <button type="submit">Submit</button>
</form>