<?php
// api/get_paragraph.php
require_once '../config/db.php';

header('Content-Type: application/json');

$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'medium';
$language = isset($_GET['language']) ? $_GET['language'] : 'english';
$useRandom = isset($_GET['random']) ? (bool)$_GET['random'] : false;
$count = isset($_GET['count']) ? max(10, (int)$_GET['count']) : 200;

try {
    if ($language === 'english' && $useRandom) {
        $raw_text = "ghost bull flying love issue check start shut near shadow serve main fail sodium horror brown wheel most hike drift answer such pretty deal belt but second queen patron column up bend pen Jew bet jump retail pocket wide own much show use shop arm far girl admire down normal truth blank so beat fact weird rely fourth pine flash fluid aid favor cow union eager desk used cotton attack van push flood way kid deem fat lock and brief feed crisis bulb egg entity need bite as boil long cancel race rabbit sole vision muscle look adopt angle colony lab smell that frame civil valley road chip hide past chef summer cool walk root works salary fly extra ensure clean mean scale rock lucky vital naval able shift advise still begin abuse policy second Jewish harm travel part guitar dawn survey sand drop joy land become toll shine senior brave fraud insert jazz depart scheme stuff myth equal murder win await music chunk secure box space excuse break useful whole draw law behave lie rough pain ankle forty merit scent love common period good but pepper shy skin league suit very found panel jar feel take sort fat beer means eat rank bear float oral window goat warm rumor zone money screw frozen resort stand cheese notion like chill object leader lower powder crack vocal burden pink kit bulk fade trip basket eighth pulse bean date beach famous lack shape spend ought make ally wine number case dish listen lose secure last divide statue these dump thus out remote drunk forth trick like daily school tape usual outlet margin spell crowd male Senate stare pack role guard blend cab grape yeah action find govern carrot bone yard ball salad drum breath data harsh tie profit print ethnic huh green drink boot arrest seat pet gas lack only shoot tale them unit cold yours bitter face pond pump flat open his body wish purple seem test share ranch credit shake thirty trim move fight public fire past rent secret view Arab river object poet pool rating file remark silk amount diary reason farmer door tough march punish grade steel meet leap swing answer throw below solar tumor flesh and/or trade female assign German easily wipe spit tired advice design ours arrow puzzle deal recipe artist strong thick model mass debate occupy master decide hard blow dress cheek host pile ride form asset fixed big set upset detail comply spray plunge hotel render into immune pro world wise twist former screen assist bath gene Muslim well low juror calm mask sheer hell hope where diet truck try refuse alter shark squad annual German bench arise method sin rescue intent fully leave chase hero across very height empire carbon launch fleet soap barn finish defeat golden agenda not range taste pair hot drive bank member me tooth warn staff guilty expert crew permit mainly than ballot tragic mean value fly happen pasta many afford duck Irish silly front rest onto dealer pose due press gold shrink turn glance about debt church known index blue cure some dough drug knife added plenty think treat helmet runner okay fork dig spill since math hey rule milk free beard guilt gender lots long mess snap sing gain fever motive fabric repair wrap ego fierce tool off lip hidden butt giant more now sector lay him global fair shape twenty cute shrug blond burn path steady firmly I after later regain actual seller hunt amid status again hunter deploy split act core pay level layer pause yell straw first cost nod color source minor glove label device scream boat senior cloth cell buddy corner palace neck name inner donate beast future appeal author step expose hole toxic simply flight laugh pick market in settle better bottle haul never lap abroad rack honor no submit virus demand slope about realm let board off stop crowd spoon cage pork vacuum signal cruise force candle ago evil freeze tomato dining relate toe route film vote flow sport help rip TV threat odds gaze top hunger melt magic soft widely shell us term it rise oppose bug drive mere quote lake any lead way ask middle rail aware teach vary floor monkey mayor reply top one eleven voting you enable black safely pile watch base deny reward mode call tissue shore tent later game OK star roll access mate logic estate all slam battle apply else depth just cite rat last bit beef fatal post maker still flee e-mail prize shower like boast weed clerk Indian link lift seal ride copy fog debut too stand tall favor kid blue lobby tune hurry real fool career shrimp attack work topic rapid over rich each evolve jet spread more baby sword her wife nation alive freely noise re stick glass sudden gift island sunny easy great radar clear red hook foot feel speed rental father remove fame Islam face gentle print web enemy then light debate ie harm month hair till third mail spring valid tribe force parent pat whip breeze open skip crash track paint watch injure talk near ok Korean bet enroll maybe opt itself slight ass shade oh roof buyer who rain lady hurt crawl nose living sea subtle two winner fight car down tribal break quit era final opera soil loop matter mood tight that small garlic as scene leaf trace stop oil humor regret hire child cap turn stack bread person word each our bolt slap raw scared side hand hence part after spite knee worry accept heat assume wrong miss local slave motor sky employ launch event gut steep tree trust whose pill sue pole over prior folk deck budget can calm sock text sexual less wow risk fit wisdom mix night apart team media spot slow blame youth afraid formal their wash hall heavy cabin pace switch United God manual legend far rally boring regime among grief oven acid hip sink highly form mud letter dozen view rhythm attend liver stuff ladder duty up whole as catch online short toilet cook rub circle guide safe funny exist cliff bishop singer lock reveal auto thread brake or preach unique ethics model owe bother detect case greet DNA visual mind till plane delay sex rush strip soup area star any well by wander ocean string client public prompt swell matter bloody";
        $text = preg_replace('/\s+/', ' ', trim($raw_text));
        $words = explode(' ', $text);
        shuffle($words);
        if (count($words) < $count) {
            $times = (int)ceil($count / max(1, count($words)));
            $extended = [];
            for ($i = 0; $i < $times; $i++) {
                $tmp = $words;
                shuffle($tmp);
                $extended = array_merge($extended, $tmp);
            }
            $words = $extended;
        }
        $content = implode(' ', array_slice($words, 0, $count));
        echo json_encode(['success' => true, 'data' => ['id' => 0, 'content' => $content]]);
        return;
    }

    $limitRows = isset($_GET['count']) ? max(1, (int)$_GET['count']) : 1;
    $stmt = $pdo->prepare("SELECT id, content FROM default_paragraphs WHERE language = ? ORDER BY RAND() LIMIT " . $limitRows);
    $stmt->execute([$language]);
    $rows = $stmt->fetchAll();

    if ($rows && count($rows) > 0) {
        if (count($rows) === 1) {
            echo json_encode(['success' => true, 'data' => $rows[0]]);
        } else {
            $combined = [];
            foreach ($rows as $r) {
                $combined[] = trim($r['content']);
            }
            $content = implode(' ', $combined);
            echo json_encode(['success' => true, 'data' => ['id' => 0, 'content' => $content]]);
        }
    } else {
        $stmt = $pdo->query("SELECT id, content FROM default_paragraphs ORDER BY RAND() LIMIT 1");
        $paragraph = $stmt->fetch();
        if ($paragraph) {
            echo json_encode(['success' => true, 'data' => $paragraph]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No paragraphs found.']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
