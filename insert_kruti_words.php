<?php
require_once 'config/db.php';

$raw_text = <<<'EOD'
:dks jktiwr gks gj tSls ladh.kZ i= varj Hk;kud uk[kqu rkfd larjs chrk fxjkuk lEink csjkstxkj NksVh ,glkl chekj 'kq#vkr xbZ la[;k x:M jktuhfrd fojks/k mBkvks &gt;xMk lkQ LFkkfir utnhd Bhd igyw Hkkjrh; oks rqjUr nsrs fd;k [kRe jax vykok Hkksaduk iSaV g"kZ ihNs MkDVj crk lkseokj HksM lewg djrk ekSdk lksuk us e/;e u"V pEep fcgkj uD'kk [kkuk lnhZ lkspuk dgk lwa?kuk fn[kk vkSjr csgrj ftUgksaus ekmaV cguk lqu lLrk jkth yxk pkSng /;ku rsy ges'kk ns'k [kjkc lkezkT;ksa f'kdLr ckotwn ckuk Iyklh gesa nnZ doj dc nkSjku ctk; vBkjg Hkfo"; teZu lksygoka lMd LoPN ekQ fxj lekIr lkexzh dgha fo'ks"kdj fpduk le&gt;rs ds mi;ksxh mM egRoiw.kZ cSaxuh fxjuk ewaxQyh mn; vksj fgju dye iwNuk vkerkSj FkksMk pkjksa mlls igkM nkfgus oLrqvksa ns[kHkky vad ;ksxnku viuh jfookj uhacw xsan eSnku ijfLFkfr tue flikgh ifjokj cts cksMZ cukuk ml pkSFkk ldrs 'kjn dky tkrh uk;dksa vkils og QkSt rFkk [kq'k caVk pkykd pkgsaxh Qwy lwtuk vkx gVkuk ogka fcYdqy dj ij eksts vafre fu;e m/kkj lkeuk ,rjkt ekStwn vkvks volj fel fy[kuk fnypLih fgUnw ckS) lLrh t:j uedhu lhuk gn csjkstxkjh rkfydk gtkj cqansy dqN cks/kd okrkZ [kkrh ugkus bafM;k ?kVuk gksrk dkyh osYyksj dkcqy dke;kc vkS"k/kky; jkbQy fpdu ,d VSDlh cl pkSMk uked v/khu drjuh cqykuk lalk/kuksa 'kq# xzUFk Hkhrj njoktk cksyk csVh igyh [khap esgur fons'kh uxj jlksbZ?kj fdjk, ikuh pkfg, if{k;ksa cSBs NksMk lsok fdlkuksa canjxkg vkanksyu o`f) lkbeu igys v/;;u j.kuhfr f}rh; Hkwjk QkeZ geus lQy clk dksfj;kbZ Lokn dBiqryh miokl Hkjus lvknr fxurh eqdkcyk cxy cPpk ns[krs gekjk pkgrs egaxk dke cknke ikap le&gt;dj Mky cnkZ'r ebZ uksV cafde ls ehy vksou egku ogha uhps fy;s vius nl ckn b'kkjk canj lykn ckj lathnk {ks= if'peh yackbZ blfy, 'kk;n tkikuh psrkouh X;kjg xkuk Lo:i ifj.kke vkyw fgjklr lfnZ;ksa lkekU; cSBk nsaxs 'k;ud{k vU; ywVk rS;kjh in iksaNuk deht rkyh ihuk lkjs dHkh ;kstuk ;fn ykSVs /kwy rkj choh rksi cqjk uhyk ekuh xje dSlk [kjksapuk gok tksMh vkuk dkjrwl tkuoj ctk vk, ekeyk cQZ ;q) tkuuk lkezkT; fopkjksa fcLrj Fkwduk xkSjh ygj laHkor;k vkf/kiR; iSny lq/kkjoknh &gt;Vdk Hkkstu ns[kk bafM;u fdUrq Lisfu'k 'kkldksa ifo= fot; mldh QSy flDds le; Hkstuk cPpksa fo'kky erHksn fepZ lcls tyuk fgj.k O;k;ke fo"k; dy Leksfdax nknh foHkhf\"kdk Lisu lq/kkjksa cdjk fldqM fczfV'k fujk'k ?kaVk nloka flrkjk miyC/k ftjkQksa fufeZr xehZ nsdj cksyrs pqjkuk vPNh bZesy f'kdkj f[kMdh da/kk j[kuk Hkrhtk I;kj ns'kksa C;kt 'kfuokj fn, yx lse nkSj egaxh fey dkxt ykxr dksVk fiNys yEcs vdsys Qksu vkids }kjk psgjk iqjkuk pqurh pkSngoka [kwclwjr yksu eq[k ykMZ vkbZ fQj ?kqVuk fdrus etcwr xka/khth nke lsV ftjkQ cSuthZ igpku 'kwU; cMk Hkwy flag lwvj Jherh ijs ryk'k fdrkc xyhpk O;kikj js[kk ftlesa vpkud geyk fy, detksj vxLr djsaxh felz mudks fganw tc c/kkbZ chloka vaxzstksa vf/kdrj Lora=rk vkxjk fnDdr i`Foh okLrfod ukjaxh dYiuk dejs dksfgek tek mldk mRiknu thjk 'kh?kz dkVuk djrs ;gka lQyrk Hkkjr [kaM ekywe twrs deh'ku [kkl l=g ekurs ekufp= bZjkuh ykxw cdfj;ksa igqpus Rofjr [kkrk /kkjk vk'p;Z ltk fu'kku Hkjkslk &gt;syuk vkbZ iw.kZr;k /kjrh pky idMuk vaxzsth ijEijk if'pe vPNk vthc yky n'keyo ghjk fdlh gky ckjg muds dksf'k'k fu'kkus bartkj ?kqlk pfdr geykoj ysdj lu ik;k yktir iwjk pfy;s ijkt; nokbZ vk tkuk jgk fy;k ,dtqV jgs rjkbu ihB ukFk jgus tgkt ;gh n'kZu dk;Zdky okg lnkf'ko xktj BaM eqag ?kaVs nksuksa xBu nkl feuV ftudk tkQj lq/kkjuk ?kksMs tkb;s djuky baXySaM jkT; Hkk"kk bdkbZ rw lqcg lhfer vkfFkZd vkrk oxhZ; fn[kkok fd;k dksbZ ek= dyk eukuk feBkbZ tkiku uhan eksgu fnu balku QuhZpj feydj ou eqxy leL;k ukiuk of.kZr igyk diMs unh l{ke QkStksa lw[kk fcanq ddMh Hkko Li'kZ tku lEcU/k ySfVu 'kkfey lqanjrk uk'kikrh lnL; /kkfeZd dk;kZy; Hkwxksy vc nksgjk leLr uksfVl rh[kk 'kqHk iDdk feyh ifjfpr 'kgj lwph rkSfy;k tkrk eaxyokj mUur pys [kkstuk m/kj cgq/kk ubZ rsjg jktxq# jkr dek;k Nqvk la;a= ekdZ ljdkj xky iqfyl x;k ?ku?kksj mUgksaus cVqvk bdykSrk yxrk f'kdk;r viuk vLohdkj pksj gksrh gd D;k iq#"kksa pyks NksM M.Mk yxHkx fpiduk bVyh vknr ywV vktknh pkj lalkj iqu% vkleku yksnh djds ckjh ilUn varfj{k vf/kos'ku mTToy vkjke vuqlkj tSlk [kku ns[kks fcuk cprs QksVks rsjgoka i;kZIr vk'khokZn tkikfu;ksa lkeus gkFk dh fnypLi otg ekQh Hkwfe gR;k,a eqyk;e loZJs"B eqfLyeksa ysrs dVkSrh etcwrh blds blls dSlh gq, ilan vaxzst fu;a=.k pwluk gksus vf/kd cpkvks yxrh 'kCnkoyh uokc ;|fi lkns /kqa/k djhch cnydj jgrk nf{k.k ysfdu tgka xk;saxs uke Hk;Hkhr fy[k nsj fopkj ?k.Vs xf.kr ;dhu vklku jks;k miyfC/k dkaxzsl ngst idM djrh jk=h /kks[kk i{kh dhft;s vfgalk tehu LFkkuh; okLro lsukvksa rF; NBk xq.kk lrg lj dku vrhr otu gkFkh lc pykus paxst vlj gYdk ekewyh vk;k nwljk fyf[kr fodkl n'kkZuk ikao pyus xSl gqbZ ;k=k iwjc ne nwjHkk"k xkSj [ksr Fkk pyrs /khjs iwjs lkfcr vkdkj [khapuk gekjs vke v'kksd vlQy lk/ku ihrh lqj{kk ifjorZu vkdk'k Vhe fdruk lq&gt;ko ueLrs leku tk fu"i{k vaMk [kksnuk ikS/kk 'kq: fHkUu ea=h foKku pan uko fuekZ.k fxjkoV 'kknh'kqnk [kjxks'k vkneh xkoa dksfguwj eqgEen lkcqu [kMk lh/kk lokjh nwljs &gt;wyuk ysuk fgeikr ?kfM;kyksa n;kyw mBk;k laxzke fcrkuk flok; fp= xgjk lkxj eqMuk okys gka deku djksxh nwj le&gt;k esjh fo'okl fljs eqfLye lyXu crk;k dkcw dg ogh vk/kk gjkrs [kqn dksguh vusdksa fcy Nkrh v[kckj tekus xBca/ku fgald tkus le`f) ikapoka flrkjksa iwaN feyrs ckag cgl eqlhcr pkfg;s x.kuk nsrh cq&gt;dj vkSj mlds 'kfu O;kikfjd xhyk Hkkjrh;ksa rktk dqekjh 'kkg vkokt lq/kkjd ekpZ cSBuk O;Lr ikmaM ckgj dfyax ldrh cus Lokxr rqe vk/kkj igqpk /kheh fy[kk mrkjuk nksLr cSlk[kh xjnu ;q) eukus ykHk ifr okil tYn lh[kus oxZ ;gwnh xbZ gksrs ukxkySaM ?kfM;ky eaxye; ge tka?k etk cksyuk bZ'oj vlarq"V ykWMZ ekg ihrk QwM fodflr 'kSEiw vlger cksy varMh bPNk jkekor lwjt pwgksa yksx dMok x;h va/ksjk loky f'kf{kr lqHkk"k gkfly dkj :lh dkVs eD[ku Hkstk dukMk lq[knso dkj.k pkan lkefjd ikl dqjlh 'ksjksa lh[kuk ck?k rqEgkjk iatkc bap iwN yk[k mUuhloka us'kuy pkdw pyk /kwfey yMuk xk;sa vklkuh LFkkiuk lezkV iguuk eSlwj voKk gkykafd tksM lkeftd djuk laHko Vkiw lekurk xnj uQjr foeku mi;ksx ns[kuk eu lkspk lQj #i Hkkyqvksa vk
EOD;

// Decode HTML entities (e.g., &gt; -> >)
$text = html_entity_decode($raw_text);

// Clean up
$text = trim($text);
if (strpos($text, ':') === 0) {
    $text = substr($text, 1);
}

// Split by spaces
$words = preg_split('/\s+/', $text);

// Chunk into 60 words
$chunks = array_chunk($words, 10);

$stmt = $pdo->prepare("INSERT INTO paragraphs (language, difficulty, title, content) VALUES (:language, :difficulty, :title, :content)");

$count = 0;
foreach ($chunks as $index => $chunk) {
    $content = implode(" ", $chunk);
    $title = "Kruti Dev Practice " . ($index + 1);
    
    $stmt->execute([
        ':language' => 'hindi_krutidev',
        ':difficulty' => 'medium',
        ':title' => $title,
        ':content' => $content
    ]);
    $count++;
}

echo "Inserted $count Kruti Dev paragraphs successfully.";

?>
