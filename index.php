<?php
// BuyMark REST API — single front controller. Mirrors the preview JSON contract.
// Route with .htaccess (all /api/* -> index.php). Prepared statements throughout.
declare(strict_types=1);

require __DIR__ . '/config/db.php';
require __DIR__ . '/config/jwt.php';
require __DIR__ . '/config/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
// Path after /api/  e.g. "shops/1", "auth/login"
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^.*/api/#', '', $uri);
$path = trim($path, '/');
$seg  = $path === '' ? [] : explode('/', $path);

// ---------- serializers ----------
function shop_row(PDO $pdo, array $s): array {
    return [
        'shop_id'      => (int)$s['shop_id'],
        'user_id'      => (int)$s['user_id'],
        'shop_name'    => $s['shop_name'],
        'shop_slug'    => $s['shop_slug'] ?? null,
        'category'     => $s['category'] ?? null,
        'offer_type'   => $s['offer_type'] ?? 'Products',
        'description'  => $s['description'] ?? null,
        'city'         => $s['city'] ?? null,
        'area'         => $s['area'] ?? null,
        'address'      => $s['address'] ?? null,
        'landmark'     => $s['landmark'] ?? null,
        'owner_name'   => $s['owner_name'] ?? null,
        'phone'        => $s['phone'] ?? null,
        'whatsapp'     => $s['whatsapp'] ?: ($s['phone'] ?? null),
        'email'        => $s['email'] ?? null,
        'shop_photo'   => image_url($s['shop_photo'] ?? null),
        'shop_logo'    => image_url(($s['shop_logo'] ?? null) ?: ($s['shop_photo'] ?? null)),
        'opening_time' => $s['opening_time'] ?? '10:00 AM',
        'closing_time' => $s['closing_time'] ?? '9:00 PM',
        'status'       => $s['status'] ?? 'pending',
        'is_verified'  => (int)($s['is_verified'] ?? 0),
        'is_pro'       => shop_is_pro($s),
        'pro_expires_at' => $s['pro_expires_at'] ?? null,
        'is_open'      => true,
        'services'     => json_decode($s['services'] ?? '[]', true) ?: [],
        'offers'       => json_decode($s['offers'] ?? '[]', true) ?: [],
    ];
}
function shop_is_pro(array $s): bool {
    if (empty($s['pro_expires_at'])) return false;
    return strtotime((string)$s['pro_expires_at']) > time();
}
function variant_row(array $v): array {
    return [
        'variant_id' => (int)$v['variant_id'],
        'product_id' => (int)$v['product_id'],
        'size'       => $v['size'] ?? null,
        'color'      => $v['color'] ?? null,
        'sku'        => $v['sku'] ?? null,
        'stock'      => (int)($v['stock'] ?? 0),
        'price'      => (float)($v['price'] ?? 0),
        'is_active'  => (bool)($v['is_active'] ?? 1),
    ];
}
function product_row(PDO $pdo, array $p, bool $withShop = false): array {
    $imgs = [];
    foreach (['product_image1','product_image2','product_image3','product_image4'] as $k)
        if (!empty($p[$k])) $imgs[] = image_url($p[$k]);
    $out = [
        'product_id'         => (int)$p['product_id'],
        'product_title'      => $p['product_title'],
        'product_discription'=> $p['product_discription'] ?? null,
        'product_keyword'    => $p['product_keyword'] ?? null,
        'category_id'        => isset($p['category_id']) ? (int)$p['category_id'] : null,
        'brand_id'           => isset($p['brand_id']) ? (int)$p['brand_id'] : null,
        'shop_id'            => isset($p['shop_id']) ? (int)$p['shop_id'] : null,
        'images'             => $imgs,
        'product_image1'     => image_url($p['product_image1'] ?? null),
        'product_price'      => (float)$p['product_price'],
        'product_old_price'  => (float)($p['product_old_price'] ?? 0),
        'Stock'              => (int)($p['Stock'] ?? 0),
    ];
    if ($withShop && !empty($p['shop_id'])) {
        $st = $pdo->prepare('SELECT * FROM shops WHERE shop_id = ?');
        $st->execute([(int)$p['shop_id']]);
        $sh = $st->fetch();
        $out['shop'] = $sh ? shop_row($pdo, $sh) : null;
        $ct = $pdo->prepare('SELECT category_title FROM categories WHERE category_id = ?');
        $ct->execute([(int)($p['category_id'] ?? 0)]);
        $out['category_title'] = ($r = $ct->fetch()) ? $r['category_title'] : null;
    }
    return $out;
}

$R = "$method /" . implode('/', $seg);

try {
// ================= AUTH =================
if ($R === 'POST /auth/register') {
    $b = body();
    $email = strtolower(trim($b['user_email'] ?? ''));
    if (!$email || strlen($b['user_password'] ?? '') < 4) fail('Enter name, email and a password (min 4 chars).');
    $c = $pdo->prepare('SELECT user_id FROM user_table WHERE user_email = ?');
    $c->execute([$email]);
    if ($c->fetch()) fail('An account with this email already exists. Please log in.', 409);
    $hash = password_hash($b['user_password'], PASSWORD_BCRYPT);
    $ins = $pdo->prepare('INSERT INTO user_table (username, user_email, user_password, user_mobile) VALUES (?,?,?,?)');
    $ins->execute([trim($b['username'] ?? ''), $email, $hash, $b['user_mobile'] ?? null]);
    $uid = (int)$pdo->lastInsertId();
    $u = ['user_id'=>$uid,'username'=>$b['username'],'user_email'=>$email,'user_mobile'=>$b['user_mobile']??null];
    ok(['token'=>jwt_issue($uid,$email),'user'=>public_user($u)], 'Welcome to BuyMark!');
}
if ($R === 'POST /auth/login') {
    $b = body();
    $email = strtolower(trim($b['user_email'] ?? ''));
    $st = $pdo->prepare('SELECT * FROM user_table WHERE user_email = ? LIMIT 1');
    $st->execute([$email]);
    $u = $st->fetch();
    if (!$u || !password_verify($b['user_password'] ?? '', $u['user_password'])) fail('Invalid email or password.', 401);
    ok(['token'=>jwt_issue((int)$u['user_id'],$u['user_email']),'user'=>public_user($u)], 'Logged in');
}
if ($R === 'GET /auth/me') { $u = require_user($pdo); ok(['user'=>public_user($u)]); }
if ($R === 'PUT /auth/profile') {
    $u = require_user($pdo); $b = body();
    $fields = []; $vals = [];
    foreach (['username','user_mobile','user_address'] as $f)
        if (isset($b[$f])) { $fields[] = "$f = ?"; $vals[] = $b[$f]; }
    if ($fields) { $vals[] = $u['user_id']; $pdo->prepare('UPDATE user_table SET '.implode(',',$fields).' WHERE user_id = ?')->execute($vals); }
    $st = $pdo->prepare('SELECT * FROM user_table WHERE user_id = ?'); $st->execute([$u['user_id']]);
    ok(['user'=>public_user($st->fetch())], 'Profile updated');
}
if ($R === 'POST /auth/logout') { require_user($pdo); ok(null, 'Logged out'); }

// ================= MASTER DATA =================
if ($R === 'GET /cities') {
    $rows = $pdo->query("SELECT DISTINCT city FROM shops WHERE status='approved' AND city<>''")->fetchAll();
    $cities = array_map(fn($r)=>$r['city'], $rows);
    foreach (['Rajnandgaon','Durg','Raipur','Indore','Bhopal','Jabalpur'] as $c)
        if (!in_array($c, $cities, true)) $cities[] = $c;
    ok(['cities'=>$cities]);
}
if ($R === 'GET /categories') {
    $rows = $pdo->query('SELECT * FROM categories ORDER BY category_id')->fetchAll();
    ok(['categories'=>array_map(fn($c)=>[
        'category_id'=>(int)$c['category_id'],'category_title'=>$c['category_title'],
        'category_image'=>image_url($c['category_image_name'] ?? null)], $rows)]);
}
if ($R === 'GET /brands') {
    $rows = $pdo->query('SELECT * FROM brands ORDER BY brand_id')->fetchAll();
    ok(['brands'=>array_map(fn($b)=>['brand_id'=>(int)$b['brand_id'],'brand_title'=>$b['brand_title']], $rows)]);
}

// ================= SHOPS =================
if ($method === 'GET' && $seg === ['shops']) {
    $where = ["status='approved'"]; $args = [];
    if (!empty($_GET['city']))     { $where[]='city = ?';          $args[]=$_GET['city']; }
    if (!empty($_GET['category'])) { $where[]='category LIKE ?';   $args[]='%'.$_GET['category'].'%'; }
    if (!empty($_GET['q']))        { $where[]='(shop_name LIKE ? OR category LIKE ? OR area LIKE ?)'; $q='%'.$_GET['q'].'%'; array_push($args,$q,$q,$q); }
    $sql = 'SELECT * FROM shops WHERE '.implode(' AND ',$where).' ORDER BY shop_id DESC';
    $st = $pdo->prepare($sql); $st->execute($args);
    $rows = $st->fetchAll();
    ok(['shops'=>array_map(fn($s)=>shop_row($pdo,$s), $rows), 'total'=>count($rows)]);
}
if ($R === 'GET /shops/mine') {
    $u = require_user($pdo);
    $st = $pdo->prepare('SELECT * FROM shops WHERE user_id = ? ORDER BY shop_id DESC'); $st->execute([$u['user_id']]);
    $out = [];
    foreach ($st->fetchAll() as $s) {
        $r = shop_row($pdo,$s);
        $pc = $pdo->prepare('SELECT COUNT(*) c FROM products WHERE shop_id = ?'); $pc->execute([$s['shop_id']]);
        $r['product_count'] = (int)$pc->fetch()['c'];
        $oc = $pdo->prepare('SELECT COUNT(*) c FROM user_orders WHERE shop_id = ?'); $oc->execute([$s['shop_id']]);
        $r['order_count'] = (int)$oc->fetch()['c'];
        $out[] = $r;
    }
    ok(['shops'=>$out]);
}
if ($method === 'GET' && count($seg) === 2 && $seg[0] === 'shops' && is_numeric($seg[1])) {
    $st = $pdo->prepare('SELECT * FROM shops WHERE shop_id = ?'); $st->execute([(int)$seg[1]]);
    $s = $st->fetch(); if (!$s) fail('Shop not found.', 404);
    $data = shop_row($pdo,$s);
    $ps = $pdo->prepare('SELECT * FROM products WHERE shop_id = ? ORDER BY product_id DESC'); $ps->execute([(int)$seg[1]]);
    $data['products'] = array_map(fn($p)=>product_row($pdo,$p), $ps->fetchAll());
    $rv = $pdo->prepare('SELECT review_id,product_id,shop_id,review_data,review_star,name FROM user_review WHERE shop_id = ? ORDER BY review_id DESC');
    $rv->execute([(int)$seg[1]]);
    $data['reviews'] = $rv->fetchAll();
    if ($data['reviews']) {
        $data['rating'] = round(array_sum(array_map(fn($r)=>(int)$r['review_star'], $data['reviews'])) / count($data['reviews']), 1);
        $data['rating_count'] = count($data['reviews']);
    }
    $fc=$pdo->prepare('SELECT COUNT(*) c FROM shop_followers WHERE shop_id=?');$fc->execute([(int)$seg[1]]);
    $data['follower_count']=(int)$fc->fetch()['c'];
    ok(['shop'=>$data]);
}
if ($method === 'POST' && $seg === ['shops']) {
    $u = require_user($pdo); $b = body();
    if (!preg_match('/^[6-9][0-9]{9}$/', $b['phone'] ?? '')) fail('Enter a valid 10-digit Indian mobile number.');
    $slug = slugify($b['shop_name'] ?? 'shop'); $base=$slug; $n=2;
    while (true){ $c=$pdo->prepare('SELECT shop_id FROM shops WHERE shop_slug=?'); $c->execute([$slug]); if(!$c->fetch()) break; $slug="$base-".$n++; }
    $ins = $pdo->prepare('INSERT INTO shops (user_id,shop_name,shop_slug,category,offer_type,description,city,area,address,landmark,owner_name,phone,whatsapp,email,shop_photo,shop_logo,opening_time,closing_time,status,is_verified) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)');
    $ins->execute([$u['user_id'],$b['shop_name'],$slug,$b['category']??'',$b['offer_type']??'Products',$b['description']??null,$b['city']??'',$b['area']??'',$b['address']??'',$b['landmark']??null,$b['owner_name']??'',$b['phone'],$b['whatsapp']??null,$b['email']??null,$b['shop_photo']??null,$b['shop_logo']??null,$b['opening_time']??'10:00 AM',$b['closing_time']??'9:00 PM','approved']);
    $sid=(int)$pdo->lastInsertId();
    $st=$pdo->prepare('SELECT * FROM shops WHERE shop_id=?'); $st->execute([$sid]);
    ok(['shop'=>shop_row($pdo,$st->fetch())], 'Shop added! It is now live on BuyMark.');
}
if ($method === 'PUT' && count($seg) === 2 && $seg[0] === 'shops' && is_numeric($seg[1])) {
    $u = require_user($pdo); $b = body(); $sid=(int)$seg[1];
    $st=$pdo->prepare('SELECT * FROM shops WHERE shop_id=?'); $st->execute([$sid]); $s=$st->fetch();
    if(!$s) fail('Shop not found.',404);
    if((int)$s['user_id'] !== (int)$u['user_id']) fail('You are not allowed to manage this shop.',403);
    $cols=['shop_name','category','offer_type','description','city','area','address','landmark','owner_name','phone','whatsapp','email','shop_photo','shop_logo','opening_time','closing_time'];
    $set=[];$vals=[]; foreach($cols as $c) if(isset($b[$c])){$set[]="$c=?";$vals[]=$b[$c];}
    if($set){$vals[]=$sid; $pdo->prepare('UPDATE shops SET '.implode(',',$set).' WHERE shop_id=?')->execute($vals);}
    $st->execute([$sid]);
    ok(['shop'=>shop_row($pdo,$st->fetch())], 'Shop updated');
}
if ($method === 'GET' && count($seg)===3 && $seg[0]==='shops' && $seg[2]==='orders') {
    $u = require_user($pdo); $sid=(int)$seg[1];
    $st=$pdo->prepare('SELECT user_id FROM shops WHERE shop_id=?'); $st->execute([$sid]); $s=$st->fetch();
    if(!$s) fail('Shop not found.',404);
    if((int)$s['user_id'] !== (int)$u['user_id']) fail("You are not allowed to view this shop's orders.",403);
    $o=$pdo->prepare('SELECT * FROM user_orders WHERE shop_id=? ORDER BY order_id DESC'); $o->execute([$sid]);
    ok(['orders'=>$o->fetchAll()]);
}

// ================= PRODUCTS =================
if ($R === 'GET /products/featured') {
    $f = $pdo->query('SELECT * FROM products ORDER BY product_id DESC LIMIT 8')->fetchAll();
    $pop = $pdo->query('SELECT * FROM products ORDER BY product_price DESC LIMIT 8')->fetchAll();
    ok(['featured'=>array_map(fn($p)=>product_row($pdo,$p,true),$f),'popular'=>array_map(fn($p)=>product_row($pdo,$p,true),$pop)]);
}
if ($method==='GET' && $seg===['products']) {
    $where=['1=1']; $args=[];
    if(!empty($_GET['category_id'])){$where[]='category_id=?';$args[]=(int)$_GET['category_id'];}
    if(!empty($_GET['shop_id'])){$where[]='shop_id=?';$args[]=(int)$_GET['shop_id'];}
    if(!empty($_GET['q'])){$where[]='(product_title LIKE ? OR product_keyword LIKE ?)';$q='%'.$_GET['q'].'%';array_push($args,$q,$q);}
    $sql='SELECT * FROM products WHERE '.implode(' AND ',$where).' ORDER BY product_id DESC';
    $st=$pdo->prepare($sql);$st->execute($args);$rows=$st->fetchAll();
    ok(['products'=>array_map(fn($p)=>product_row($pdo,$p,true),$rows),'total'=>count($rows)]);
}
if ($method==='GET' && count($seg)===2 && $seg[0]==='products' && is_numeric($seg[1])) {
    $st=$pdo->prepare('SELECT * FROM products WHERE product_id=?');$st->execute([(int)$seg[1]]);$p=$st->fetch();
    if(!$p) fail('Product not found.',404);
    $data=product_row($pdo,$p,true);
    $sz=$pdo->prepare('SELECT size_name,stock FROM product_sizes WHERE product_id=?');$sz->execute([(int)$seg[1]]);
    $data['sizes']=$sz->fetchAll();
    $cl=$pdo->prepare('SELECT color_name,color_image FROM product_colors WHERE product_id=?');$cl->execute([(int)$seg[1]]);
    $data['colors']=array_map(fn($c)=>['color_name'=>$c['color_name'],'color_image'=>image_url($c['color_image'])],$cl->fetchAll());
    $vr=$pdo->prepare('SELECT * FROM product_variants WHERE product_id=? AND is_active=1 ORDER BY variant_id');$vr->execute([(int)$seg[1]]);
    $vrows=$vr->fetchAll();
    $data['variants']=array_map(fn($v)=>variant_row($v),$vrows);
    $data['has_variants']=count($vrows)>0;
    if($vrows){$data['variant_stock_total']=array_sum(array_map(fn($v)=>(int)$v['stock'],$vrows));}
    $rv=$pdo->prepare('SELECT review_id,product_id,review_data,review_star,name FROM user_review WHERE product_id=? ORDER BY review_id DESC');$rv->execute([(int)$seg[1]]);
    $data['reviews']=$rv->fetchAll();
    if($data['reviews']){$data['rating']=round(array_sum(array_map(fn($r)=>(int)$r['review_star'],$data['reviews']))/count($data['reviews']),1);$data['rating_count']=count($data['reviews']);}
    ok(['product'=>$data]);
}
if ($method==='POST' && count($seg)===3 && $seg[0]==='shops' && $seg[2]==='products') {
    $u=require_user($pdo);$b=body();$sid=(int)$seg[1];
    $st=$pdo->prepare('SELECT * FROM shops WHERE shop_id=?');$st->execute([$sid]);$s=$st->fetch();
    if(!$s) fail('Shop not found.',404);
    if((int)$s['user_id']!==(int)$u['user_id']) fail('You can only add products to your own shop.',403);
    if(!shop_is_pro($s)){
        $cc=$pdo->prepare('SELECT COUNT(*) c FROM products WHERE shop_id=? AND deleted_at IS NULL');$cc->execute([$sid]);
        if((int)$cc->fetch()['c'] >= 50)
            respond(false,'50 product limit reached. Upgrade to Pro for unlimited products or delete an existing product.',['code'=>'PRODUCT_LIMIT_REACHED','limit'=>50],403);
    }
    $ins=$pdo->prepare('INSERT INTO products (product_title,product_discription,product_keyword,category_id,brand_id,shop_id,product_image1,product_price,product_old_price,Stock) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $ins->execute([$b['product_title'],$b['product_discription']??null,$b['product_keyword']??null,$b['category_id']??null,$b['brand_id']??null,$sid,$b['product_image1']??null,(float)($b['product_price']??0),(float)($b['product_old_price']??0),(int)($b['Stock']??0)]);
    $pid=(int)$pdo->lastInsertId();$g=$pdo->prepare('SELECT * FROM products WHERE product_id=?');$g->execute([$pid]);
    // Notify followers of this shop (one each).
    $fol=$pdo->prepare('SELECT user_id FROM shop_followers WHERE shop_id=?');$fol->execute([$sid]);
    $ntitle='New at '.$s['shop_name'];$nimg=image_url($b['product_image1']??null);
    $ni=$pdo->prepare('INSERT INTO notifications (user_id,type,title,message,image,reference_id,reference_type,is_read) VALUES (?,?,?,?,?,?,?,0)');
    foreach($fol->fetchAll() as $frow){ $ni->execute([(int)$frow['user_id'],'new_product',$ntitle,$b['product_title'],$nimg,$pid,'product']); }
    ok(['product'=>product_row($pdo,$g->fetch(),true)], 'Product added');
}

// ================= PRODUCT VARIANTS =================
if ($method==='GET' && count($seg)===3 && $seg[0]==='products' && $seg[2]==='variants') {
    $u=require_user($pdo);$pid=(int)$seg[1];
    $chk=$pdo->prepare('SELECT s.user_id FROM products p JOIN shops s ON s.shop_id=p.shop_id WHERE p.product_id=?');$chk->execute([$pid]);$o=$chk->fetch();
    if(!$o) fail('Product not found.',404);
    if((int)$o['user_id']!==(int)$u['user_id']) fail("You can only manage your own shop's products.",403);
    $vr=$pdo->prepare('SELECT * FROM product_variants WHERE product_id=? AND is_active=1 ORDER BY variant_id');$vr->execute([$pid]);
    ok(['variants'=>array_map(fn($v)=>variant_row($v),$vr->fetchAll())]);
}
if ($method==='POST' && count($seg)===3 && $seg[0]==='products' && $seg[2]==='variants') {
    $u=require_user($pdo);$b=body();$pid=(int)$seg[1];
    $chk=$pdo->prepare('SELECT s.user_id FROM products p JOIN shops s ON s.shop_id=p.shop_id WHERE p.product_id=?');$chk->execute([$pid]);$o=$chk->fetch();
    if(!$o) fail('Product not found.',404);
    if((int)$o['user_id']!==(int)$u['user_id']) fail("You can only manage your own shop's products.",403);
    if(empty($b['size']) && empty($b['color'])) fail('A variant needs at least a size or a colour.');
    $ins=$pdo->prepare('INSERT INTO product_variants (product_id,size,color,sku,stock,price,is_active) VALUES (?,?,?,?,?,?,1)');
    $ins->execute([$pid,$b['size']??null,$b['color']??null,$b['sku']??null,(int)($b['stock']??0),(float)($b['price']??0)]);
    $vid=(int)$pdo->lastInsertId();$g=$pdo->prepare('SELECT * FROM product_variants WHERE variant_id=?');$g->execute([$vid]);
    ok(['variant'=>variant_row($g->fetch())], 'Variant added');
}
if ($method==='PUT' && count($seg)===2 && $seg[0]==='variants' && is_numeric($seg[1])) {
    $u=require_user($pdo);$b=body();$vid=(int)$seg[1];
    $st=$pdo->prepare('SELECT v.*, s.user_id owner FROM product_variants v JOIN products p ON p.product_id=v.product_id JOIN shops s ON s.shop_id=p.shop_id WHERE v.variant_id=?');$st->execute([$vid]);$v=$st->fetch();
    if(!$v) fail('Variant not found.',404);
    if((int)$v['owner']!==(int)$u['user_id']) fail("You can only manage your own shop's products.",403);
    $pdo->prepare('UPDATE product_variants SET size=?,color=?,sku=?,stock=?,price=? WHERE variant_id=?')
        ->execute([$b['size']??null,$b['color']??null,$b['sku']??null,(int)($b['stock']??0),(float)($b['price']??0),$vid]);
    $g=$pdo->prepare('SELECT * FROM product_variants WHERE variant_id=?');$g->execute([$vid]);
    ok(['variant'=>variant_row($g->fetch())], 'Variant updated');
}
if ($method==='DELETE' && count($seg)===2 && $seg[0]==='variants' && is_numeric($seg[1])) {
    $u=require_user($pdo);$vid=(int)$seg[1];
    $st=$pdo->prepare('SELECT s.user_id owner FROM product_variants v JOIN products p ON p.product_id=v.product_id JOIN shops s ON s.shop_id=p.shop_id WHERE v.variant_id=?');$st->execute([$vid]);$v=$st->fetch();
    if(!$v) fail('Variant not found.',404);
    if((int)$v['owner']!==(int)$u['user_id']) fail("You can only manage your own shop's products.",403);
    $pdo->prepare('UPDATE product_variants SET is_active=0 WHERE variant_id=?')->execute([$vid]);
    ok(null, 'Variant removed');
}

// ================= SUBSCRIPTION / PRO / RAZORPAY =================
if ($method==='GET' && count($seg)===3 && $seg[0]==='shops' && $seg[2]==='subscription') {
    $u=require_user($pdo);$sid=(int)$seg[1];
    $st=$pdo->prepare('SELECT * FROM shops WHERE shop_id=?');$st->execute([$sid]);$s=$st->fetch();
    if(!$s) fail('Shop not found.',404);
    if((int)$s['user_id']!==(int)$u['user_id']) fail("You don't have permission to access this.",403);
    $cc=$pdo->prepare('SELECT COUNT(*) c FROM products WHERE shop_id=? AND deleted_at IS NULL');$cc->execute([$sid]);
    $count=(int)$cc->fetch()['c']; $isPro=shop_is_pro($s);
    ok(['is_pro'=>$isPro,'pro_expires_at'=>$s['pro_expires_at']??null,'product_count'=>$count,
        'limit'=>$isPro?null:50,'remaining'=>$isPro?null:max(0,50-$count),
        'price_inr'=>99,'plan'=>'BuyMark Pro (Monthly)','payment_ready'=>razorpay_configured()]);
}
if ($method==='POST' && count($seg)===4 && $seg[0]==='shops' && $seg[2]==='billing' && $seg[3]==='order') {
    $u=require_user($pdo);$sid=(int)$seg[1];
    $st=$pdo->prepare('SELECT * FROM shops WHERE shop_id=?');$st->execute([$sid]);$s=$st->fetch();
    if(!$s || (int)$s['user_id']!==(int)$u['user_id']) fail("You don't have permission to access this.",403);
    if(!razorpay_configured()) fail('Online payment is not set up yet. Please add your Razorpay keys.',503);
    $receipt='pro_'.$sid.'_'.time();
    $payload=json_encode(['amount'=>9900,'currency'=>'INR','receipt'=>$receipt,'payment_capture'=>1,'notes'=>['shop_id'=>(string)$sid,'product'=>'pro_30_days']]);
    $ch=curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,
        CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_USERPWD=>RZP_KEY_ID.':'.RZP_KEY_SECRET,CURLOPT_TIMEOUT=>15]);
    $resp=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    if($resp===false || $code>=400) fail('Could not start payment. Please try again.',502);
    $order=json_decode($resp,true);
    $pdo->prepare('INSERT INTO razorpay_orders (shop_id,user_id,razorpay_order_id,amount,currency,status) VALUES (?,?,?,?,?,?)')
        ->execute([$sid,$u['user_id'],$order['id'],9900,'INR','created']);
    ok(['key_id'=>RZP_KEY_ID,'order_id'=>$order['id'],'amount'=>9900,'currency'=>'INR']);
}
if ($method==='POST' && count($seg)===4 && $seg[0]==='shops' && $seg[2]==='billing' && $seg[3]==='verify') {
    $u=require_user($pdo);$sid=(int)$seg[1];$b=body();
    $st=$pdo->prepare('SELECT * FROM razorpay_orders WHERE razorpay_order_id=? AND user_id=?');$st->execute([$b['razorpay_order_id']??'',$u['user_id']]);$order=$st->fetch();
    if(!$order) fail('Payment order not found.',404);
    $expected=hash_hmac('sha256',($b['razorpay_order_id']??'').'|'.($b['razorpay_payment_id']??''),RZP_KEY_SECRET);
    if(!hash_equals($expected,$b['razorpay_signature']??'')) fail('Payment could not be verified.',400);
    $sh=$pdo->prepare('SELECT * FROM shops WHERE shop_id=?');$sh->execute([$sid]);$s=$sh->fetch();
    $start=time(); if(!empty($s['pro_expires_at']) && strtotime($s['pro_expires_at'])>time()) $start=strtotime($s['pro_expires_at']);
    $until=date('Y-m-d H:i:s',$start+30*86400);
    if($order['status']!=='paid'){
        $pdo->prepare('UPDATE razorpay_orders SET status=?,payment_id=?,paid_at=NOW() WHERE id=? AND status<>?')->execute(['paid',$b['razorpay_payment_id']??'',$order['id'],'paid']);
        $pdo->prepare('UPDATE shops SET is_pro=1,pro_expires_at=? WHERE shop_id=?')->execute([$until,$sid]);
        $pdo->prepare('INSERT INTO subscriptions (shop_id,user_id,plan,amount,status,start_at,end_at,payment_id,order_id) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$sid,$u['user_id'],'Pro Monthly',99,'active',date('Y-m-d H:i:s',$start),$until,$b['razorpay_payment_id']??'',$b['razorpay_order_id']??'']);
    }
    ok(['is_pro'=>true,'pro_expires_at'=>$until], "You're now on BuyMark Pro!");
}
if ($R === 'GET /billing/checkout') {
    header('Content-Type: text/html; charset=utf-8');
    $key=htmlspecialchars($_GET['key_id']??'',ENT_QUOTES);$oid=htmlspecialchars($_GET['order_id']??'',ENT_QUOTES);$amt=(int)($_GET['amount']??0);
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        .'<body style="margin:0;background:#111;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh"><div>Loading secure payment&hellip;</div>'
        .'<script src="https://checkout.razorpay.com/v1/checkout.js"></script><script>'
        .'function send(o){if(window.ReactNativeWebView){window.ReactNativeWebView.postMessage(JSON.stringify(o));}}'
        .'try{var rzp=new Razorpay({key:"'.$key.'",order_id:"'.$oid.'",amount:'.$amt.',currency:"INR",name:"BuyMark Pro",description:"Pro subscription - 30 days",theme:{color:"#FFC400"},'
        .'handler:function(r){send({razorpay_payment_id:r.razorpay_payment_id,razorpay_order_id:r.razorpay_order_id,razorpay_signature:r.razorpay_signature});},'
        .'modal:{ondismiss:function(){send({cancelled:true});}}});'
        .'rzp.on("payment.failed",function(r){send({failed:true,message:(r.error&&r.error.description)||"Payment failed"});});rzp.open();'
        .'}catch(e){send({failed:true,message:"Could not open payment"});}</script></body></html>';
    exit;
}
if ($method==='PUT' && count($seg)===2 && $seg[0]==='products' && is_numeric($seg[1])) {
    $u=require_user($pdo);$b=body();$pid=(int)$seg[1];
    $st=$pdo->prepare('SELECT p.*, s.user_id owner FROM products p JOIN shops s ON s.shop_id=p.shop_id WHERE p.product_id=?');$st->execute([$pid]);$p=$st->fetch();
    if(!$p) fail('Product not found.',404);
    if((int)$p['owner']!==(int)$u['user_id']) fail("You can only edit your own shop's products.",403);
    $cols=['product_title','product_discription','product_keyword','category_id','brand_id','product_image1','product_price','product_old_price','Stock'];
    $set=[];$vals=[];foreach($cols as $c) if(isset($b[$c])){$set[]="$c=?";$vals[]=$b[$c];}
    if($set){$vals[]=$pid;$pdo->prepare('UPDATE products SET '.implode(',',$set).' WHERE product_id=?')->execute($vals);}
    $g=$pdo->prepare('SELECT * FROM products WHERE product_id=?');$g->execute([$pid]);
    ok(['product'=>product_row($pdo,$g->fetch(),true)], 'Product updated');
}
if ($method==='DELETE' && count($seg)===2 && $seg[0]==='products' && is_numeric($seg[1])) {
    $u=require_user($pdo);$pid=(int)$seg[1];
    $st=$pdo->prepare('SELECT p.shop_id, s.user_id owner FROM products p JOIN shops s ON s.shop_id=p.shop_id WHERE p.product_id=?');$st->execute([$pid]);$p=$st->fetch();
    if(!$p) fail('Product not found.',404);
    if((int)$p['owner']!==(int)$u['user_id']) fail("You can only delete your own shop's products.",403);
    // Soft delete (requires deleted_at column from migration).
    $pdo->prepare('UPDATE products SET deleted_at = NOW() WHERE product_id=?')->execute([$pid]);
    ok(null, 'Product removed');
}

// ================= SEARCH =================
if ($R === 'GET /search') {
    $q='%'.($_GET['q']??'').'%';
    $ss=$pdo->prepare("SELECT * FROM shops WHERE status='approved' AND (shop_name LIKE ? OR category LIKE ? OR area LIKE ?) LIMIT 10");
    $ss->execute([$q,$q,$q]);
    $ps=$pdo->prepare('SELECT * FROM products WHERE product_title LIKE ? OR product_keyword LIKE ? LIMIT 12');
    $ps->execute([$q,$q]);
    ok(['shops'=>array_map(fn($s)=>shop_row($pdo,$s),$ss->fetchAll()),'products'=>array_map(fn($p)=>product_row($pdo,$p,true),$ps->fetchAll())]);
}

// ================= WISHLIST =================
if ($R === 'GET /wishlist') {
    $u=require_user($pdo);
    $st=$pdo->prepare('SELECT p.* FROM wishlist w JOIN products p ON p.product_id=w.product_id WHERE w.owner_key=?');
    $st->execute(['user:'.$u['user_id']]);
    ok(['products'=>array_map(fn($p)=>product_row($pdo,$p,true),$st->fetchAll())]);
}
if ($method==='POST' && count($seg)===2 && $seg[0]==='wishlist') {
    $u=require_user($pdo);$pid=(int)$seg[1];$key='user:'.$u['user_id'];
    $c=$pdo->prepare('SELECT wishlist_id FROM wishlist WHERE owner_key=? AND product_id=?');$c->execute([$key,$pid]);
    if(!$c->fetch()) $pdo->prepare('INSERT INTO wishlist (product_id,ip_address,owner_key) VALUES (?,?,?)')->execute([$pid,$_SERVER['REMOTE_ADDR']??'',$key]);
    ok(null,'Added to wishlist');
}
if ($method==='DELETE' && count($seg)===2 && $seg[0]==='wishlist') {
    $u=require_user($pdo);$pid=(int)$seg[1];
    $pdo->prepare('DELETE FROM wishlist WHERE owner_key=? AND product_id=?')->execute(['user:'.$u['user_id'],$pid]);
    ok(null,'Removed from wishlist');
}

// ================= CART (one shop per cart) =================
if ($R === 'GET /cart') {
    $u=require_user($pdo);$key='user:'.$u['user_id'];
    $st=$pdo->prepare('SELECT c.cart_id,c.quantity,c.size_name,c.color_name,c.variant_id,c.product_price AS cart_price,p.product_id,p.product_title,p.product_image1,p.shop_id,v.sku AS variant_sku,v.price AS variant_price FROM cart_details c JOIN products p ON p.product_id=c.product_id LEFT JOIN product_variants v ON v.variant_id=c.variant_id WHERE c.cart_owner=? ORDER BY c.cart_id');
    $st->execute([$key]);$items=[];$sub=0;$shopId=null;
    foreach($st->fetchAll() as $r){
        $unit=$r['variant_id']?(float)($r['variant_price']!==null?$r['variant_price']:$r['cart_price']):(float)$r['cart_price'];
        $line=$unit*$r['quantity'];$sub+=$line;$shopId=$r['shop_id'];
        $items[]=['cart_id'=>(int)$r['cart_id'],'product_id'=>(int)$r['product_id'],'product_title'=>$r['product_title'],'product_image1'=>image_url($r['product_image1']),'product_price'=>$unit,'quantity'=>(int)$r['quantity'],'size_name'=>$r['size_name'],'color_name'=>$r['color_name'],'sku'=>$r['variant_sku'],'variant_id'=>$r['variant_id']?(int)$r['variant_id']:null,'line_total'=>$line,'shop_id'=>(int)$r['shop_id']];}
    $shop=null; if($shopId){$s=$pdo->prepare('SELECT * FROM shops WHERE shop_id=?');$s->execute([$shopId]);$sr=$s->fetch();$shop=$sr?shop_row($pdo,$sr):null;}
    $del=($sub==0||$sub>=999)?0:49;
    ok(['items'=>$items,'subtotal'=>$sub,'delivery'=>$del,'total'=>$sub+$del,'shop'=>$shop]);
}
if ($method==='POST' && $seg===['cart']) {
    $u=require_user($pdo);$b=body();$key='user:'.$u['user_id'];$pid=(int)$b['product_id'];
    $pr=$pdo->prepare('SELECT * FROM products WHERE product_id=?');$pr->execute([$pid]);$p=$pr->fetch();
    if(!$p) fail('Product not found.',404);
    // Variant resolution + stock validation.
    $vs=$pdo->prepare('SELECT * FROM product_variants WHERE product_id=? AND is_active=1');$vs->execute([$pid]);$variants=$vs->fetchAll();
    $variantId=null;$unit=(float)$p['product_price'];$sizeName=$b['size_name']??null;$colorName=$b['color_name']??null;$available=(int)$p['Stock'];
    if($variants){
        if(empty($b['variant_id'])) fail('Please select an available size/colour option.');
        $variant=null; foreach($variants as $v){ if((int)$v['variant_id']===(int)$b['variant_id']){$variant=$v;break;} }
        if(!$variant) fail('The selected option is unavailable. Please choose another.');
        $variantId=(int)$variant['variant_id'];$unit=(float)$variant['price'];$sizeName=$variant['size'];$colorName=$variant['color'];$available=(int)$variant['stock'];
        if($available<=0) fail('This variant is currently out of stock.');
    } else if($available<=0) { fail('This product is currently out of stock.'); }
    // One shop per cart.
    $ex=$pdo->prepare('SELECT c.*,p.shop_id FROM cart_details c JOIN products p ON p.product_id=c.product_id WHERE c.cart_owner=?');$ex->execute([$key]);$rows=$ex->fetchAll();
    if($rows && (int)$rows[0]['shop_id'] !== (int)$p['shop_id']){
        if(empty($b['force'])) respond(false,'Your cart contains products from another shop. Please clear your current cart before adding products from this shop.',['conflict'=>true],409);
        $pdo->prepare('DELETE FROM cart_details WHERE cart_owner=?')->execute([$key]);
    }
    $q=(int)($b['quantity']??1);
    // Merge duplicates (same product + variant) and prevent overselling.
    $dq=$pdo->prepare('SELECT cart_id,quantity FROM cart_details WHERE cart_owner=? AND product_id=? AND ((variant_id IS NULL AND ? IS NULL) OR variant_id=?)');
    $dq->execute([$key,$pid,$variantId,$variantId]);$dup=$dq->fetch();
    $have=$dup?(int)$dup['quantity']:0;
    if($have+$q>$available) fail($available?('Only '.$available.' left in stock.'):'This variant is currently out of stock.');
    if($dup){ $pdo->prepare('UPDATE cart_details SET quantity=quantity+? WHERE cart_id=?')->execute([$q,$dup['cart_id']]); }
    else {
        $ins=$pdo->prepare('INSERT INTO cart_details (product_id,variant_id,product_price,total_price,ip_address,cart_owner,quantity,size_name,color_name) VALUES (?,?,?,?,?,?,?,?,?)');
        $ins->execute([$pid,$variantId,$unit,$unit*$q,$_SERVER['REMOTE_ADDR']??'',$key,$q,$sizeName,$colorName]);
    }
    ok(null,'Added to cart');
}
if ($method==='PUT' && count($seg)===2 && $seg[0]==='cart') {
    $u=require_user($pdo);$b=body();$q=(int)$b['quantity'];
    if($q<=0) $pdo->prepare('DELETE FROM cart_details WHERE cart_id=? AND cart_owner=?')->execute([(int)$seg[1],'user:'.$u['user_id']]);
    else $pdo->prepare('UPDATE cart_details SET quantity=? WHERE cart_id=? AND cart_owner=?')->execute([$q,(int)$seg[1],'user:'.$u['user_id']]);
    ok(null,'Cart updated');
}
if ($method==='DELETE' && count($seg)===2 && $seg[0]==='cart') {
    $u=require_user($pdo);$pdo->prepare('DELETE FROM cart_details WHERE cart_id=? AND cart_owner=?')->execute([(int)$seg[1],'user:'.$u['user_id']]);
    ok(null,'Removed');
}
if ($method==='DELETE' && $seg===['cart']) {
    $u=require_user($pdo);$pdo->prepare('DELETE FROM cart_details WHERE cart_owner=?')->execute(['user:'.$u['user_id']]);
    ok(null,'Cart cleared');
}

// ================= ORDERS =================
if ($method==='POST' && $seg===['orders']) {
    $u=require_user($pdo);$b=body();$key='user:'.$u['user_id'];
    $st=$pdo->prepare('SELECT c.quantity,c.size_name,c.color_name,c.variant_id,c.product_price AS cart_price,p.product_id,p.product_title,p.shop_id,p.Stock AS prod_stock,v.sku AS variant_sku,v.price AS variant_price,v.stock AS variant_stock FROM cart_details c JOIN products p ON p.product_id=c.product_id LEFT JOIN product_variants v ON v.variant_id=c.variant_id WHERE c.cart_owner=?');
    $st->execute([$key]);$rows=$st->fetchAll();
    if(!$rows) fail('Your cart is empty.');
    $sub=0;$shopId=$rows[0]['shop_id'];$invoice=(int)date('mdHis');
    foreach($rows as $r){$unit=$r['variant_id']?(float)($r['variant_price']!==null?$r['variant_price']:$r['cart_price']):(float)$r['cart_price'];$sub+=$unit*$r['quantity'];}
    $del=$sub>=999?0:49;$total=$sub+$del;
    foreach($rows as $r){
        $unit=$r['variant_id']?(float)($r['variant_price']!==null?$r['variant_price']:$r['cart_price']):(float)$r['cart_price'];
        // Server-side stock guard (prevent overselling / negative stock).
        if($r['variant_id']){ if((int)$r['variant_stock'] < (int)$r['quantity']) fail('This variant is currently out of stock.'); }
        else if((int)$r['prod_stock'] < (int)$r['quantity']) fail('This product is currently out of stock.');
        $ins=$pdo->prepare('INSERT INTO user_orders (user_id,product_id,shop_id,variant_id,size_name,color_name,sku,ammount_due,invoice_number,total_products,order_status,product_name,product_price,quantity,customer_name,customer_email,shipping_address1,shipping_city,shipping_phone,payment_mode,payment_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $ins->execute([$u['user_id'],$r['product_id'],$shopId,$r['variant_id']?:null,$r['size_name'],$r['color_name'],$r['variant_sku'],$total,$invoice,count($rows),'Confirmed',$r['product_title'],$unit,$r['quantity'],$b['customer_name']??'',$b['customer_email']??null,$b['shipping_address1']??'',$b['shipping_city']??'',$b['shipping_phone']??'',$b['payment_mode']??'COD',($b['payment_mode']??'COD')==='COD'?'COD Pending':'Paid']);
        if($r['variant_id']) $pdo->prepare('UPDATE product_variants SET stock=stock-? WHERE variant_id=? AND stock>=?')->execute([$r['quantity'],$r['variant_id'],$r['quantity']]);
        else $pdo->prepare('UPDATE products SET Stock=Stock-? WHERE product_id=? AND Stock>=?')->execute([$r['quantity'],$r['product_id'],$r['quantity']]);
    }
    $oid=(int)$pdo->lastInsertId();
    $pdo->prepare('DELETE FROM cart_details WHERE cart_owner=?')->execute([$key]);
    ok(['order'=>['order_id'=>$oid,'invoice_number'=>$invoice,'amount_due'=>$total,'total_products'=>count($rows),'order_status'=>'Confirmed','payment_mode'=>$b['payment_mode']??'COD']], 'Order placed successfully!');
}
if ($R === 'GET /orders') {
    $u=require_user($pdo);
    $st=$pdo->prepare('SELECT * FROM user_orders WHERE user_id=? GROUP BY invoice_number ORDER BY order_id DESC');$st->execute([$u['user_id']]);
    ok(['orders'=>$st->fetchAll()]);
}
if ($method==='GET' && count($seg)===2 && $seg[0]==='orders' && is_numeric($seg[1])) {
    $u=require_user($pdo);
    $st=$pdo->prepare('SELECT * FROM user_orders WHERE order_id=? AND user_id=?');$st->execute([(int)$seg[1],$u['user_id']]);
    $o=$st->fetch();if(!$o) fail('Order not found.',404);
    $items=$pdo->prepare('SELECT * FROM user_orders WHERE invoice_number=? AND user_id=?');$items->execute([$o['invoice_number'],$u['user_id']]);
    $o['items']=array_map(fn($i)=>['product_id'=>(int)$i['product_id'],'product_title'=>$i['product_name'],'product_price'=>(float)$i['product_price'],'quantity'=>(int)$i['quantity'],'size_name'=>$i['size_name']??null,'color_name'=>$i['color_name']??null,'sku'=>$i['sku']??null,'line_total'=>(float)$i['product_price']*$i['quantity']],$items->fetchAll());
    ok(['order'=>$o]);
}

// ================= REVIEWS =================
if ($method === 'POST' && $seg === ['reviews']) {
    $u = require_user($pdo); $b = body();
    $pid = isset($b['product_id']) ? (int)$b['product_id'] : null;
    $sid = isset($b['shop_id']) ? (int)$b['shop_id'] : null;
    if (!$pid && !$sid) fail('A product or shop is required to review.');
    $star = max(1, min(5, (int)($b['review_star'] ?? 5)));
    $pdo->prepare('INSERT INTO user_review (product_id, shop_id, review_data, review_star, name, email) VALUES (?,?,?,?,?,?)')
        ->execute([$pid, $sid, $b['review_data'] ?? '', $star, $u['username'], $u['user_email']]);
    ok(null, 'Thanks for your review!');
}

// ================= IMAGE UPLOAD =================
if ($method === 'POST' && $seg === ['upload']) {
    $u = require_user($pdo); $b = body();
    $raw = $b['image_base64'] ?? '';
    if (strpos($raw, ',') !== false) $raw = substr($raw, strpos($raw, ',') + 1);
    $data = base64_decode($raw, true);
    if ($data === false) fail('Invalid image data.');
    if (strlen($data) > 8 * 1024 * 1024) fail('Image must be 8 MB or smaller.');
    $ext = strtolower(ltrim($b['ext'] ?? 'jpg', '.'));
    if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) $ext = 'jpg';
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $name = 'upload_' . (int)$u['user_id'] . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (file_put_contents("$dir/$name", $data) === false) fail('Could not save image.', 500);
    ok(['name' => $name, 'url' => image_url($name)], 'Uploaded');
}

// ================= FOLLOW SHOP =================
if ($method==='POST' && count($seg)===3 && $seg[0]==='shops' && $seg[2]==='follow') {
    $u=require_user($pdo);$sid=(int)$seg[1];
    $c=$pdo->prepare('SELECT shop_id FROM shops WHERE shop_id=?');$c->execute([$sid]); if(!$c->fetch()) fail('Shop not found.',404);
    $pdo->prepare('INSERT IGNORE INTO shop_followers (shop_id,user_id) VALUES (?,?)')->execute([$sid,$u['user_id']]);
    $cc=$pdo->prepare('SELECT COUNT(*) c FROM shop_followers WHERE shop_id=?');$cc->execute([$sid]);
    ok(['following'=>true,'follower_count'=>(int)$cc->fetch()['c']],'Following');
}
if ($method==='DELETE' && count($seg)===3 && $seg[0]==='shops' && $seg[2]==='follow') {
    $u=require_user($pdo);$sid=(int)$seg[1];
    $pdo->prepare('DELETE FROM shop_followers WHERE shop_id=? AND user_id=?')->execute([$sid,$u['user_id']]);
    $cc=$pdo->prepare('SELECT COUNT(*) c FROM shop_followers WHERE shop_id=?');$cc->execute([$sid]);
    ok(['following'=>false,'follower_count'=>(int)$cc->fetch()['c']],'Unfollowed');
}
if ($method==='GET' && count($seg)===3 && $seg[0]==='shops' && $seg[2]==='follow') {
    $u=require_user($pdo);$sid=(int)$seg[1];
    $f=$pdo->prepare('SELECT id FROM shop_followers WHERE shop_id=? AND user_id=?');$f->execute([$sid,$u['user_id']]);
    $cc=$pdo->prepare('SELECT COUNT(*) c FROM shop_followers WHERE shop_id=?');$cc->execute([$sid]);
    ok(['following'=>(bool)$f->fetch(),'follower_count'=>(int)$cc->fetch()['c']]);
}

// ================= NOTIFICATIONS =================
if ($R === 'GET /notifications') {
    $u=require_user($pdo);
    $st=$pdo->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY notification_id DESC LIMIT 100');$st->execute([$u['user_id']]);
    $rows=$st->fetchAll();
    $uc=$pdo->prepare('SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0');$uc->execute([$u['user_id']]);
    ok(['notifications'=>array_map(fn($n)=>['notification_id'=>(int)$n['notification_id'],'type'=>$n['type'],'title'=>$n['title'],'message'=>$n['message'],'image'=>$n['image'],'reference_id'=>$n['reference_id']?(int)$n['reference_id']:null,'reference_type'=>$n['reference_type'],'is_read'=>(bool)$n['is_read'],'created_at'=>$n['created_at']],$rows),'unread_count'=>(int)$uc->fetch()['c']]);
}
if ($R === 'GET /notifications/unread-count') {
    $u=require_user($pdo);$uc=$pdo->prepare('SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0');$uc->execute([$u['user_id']]);
    ok(['unread_count'=>(int)$uc->fetch()['c']]);
}
if ($R === 'POST /notifications/read-all') {
    $u=require_user($pdo);$pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=? AND is_read=0')->execute([$u['user_id']]);
    ok(null,'All marked read');
}
if ($method==='POST' && count($seg)===3 && $seg[0]==='notifications' && $seg[2]==='read') {
    $u=require_user($pdo);$pdo->prepare('UPDATE notifications SET is_read=1 WHERE notification_id=? AND user_id=?')->execute([(int)$seg[1],$u['user_id']]);
    ok(null,'Marked read');
}

// ================= CUSTOMER ADDRESSES =================
if ($R === 'GET /addresses') {
    $u=require_user($pdo);
    $st=$pdo->prepare('SELECT * FROM customer_addresses WHERE user_id=? AND deleted_at IS NULL ORDER BY is_default DESC, address_id DESC');$st->execute([$u['user_id']]);
    ok(['addresses'=>array_map(fn($a)=>['address_id'=>(int)$a['address_id'],'full_name'=>$a['full_name'],'mobile'=>$a['mobile'],'house'=>$a['house'],'street'=>$a['street'],'landmark'=>$a['landmark'],'city'=>$a['city'],'state'=>$a['state'],'pincode'=>$a['pincode'],'type'=>$a['type'],'is_default'=>(bool)$a['is_default']],$st->fetchAll())]);
}
if ($method==='POST' && $seg===['addresses']) {
    $u=require_user($pdo);$b=body();
    if(!preg_match('/^[6-9][0-9]{9}$/', $b['mobile']??'')) fail('Please enter a valid 10-digit mobile number.');
    $cc=$pdo->prepare('SELECT COUNT(*) c FROM customer_addresses WHERE user_id=? AND deleted_at IS NULL');$cc->execute([$u['user_id']]);
    $makeDefault = !empty($b['is_default']) || (int)$cc->fetch()['c']===0;
    if($makeDefault) $pdo->prepare('UPDATE customer_addresses SET is_default=0 WHERE user_id=?')->execute([$u['user_id']]);
    $ins=$pdo->prepare('INSERT INTO customer_addresses (user_id,full_name,mobile,house,street,landmark,city,state,pincode,type,is_default) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    $ins->execute([$u['user_id'],$b['full_name']??'',$b['mobile']??'',$b['house']??null,$b['street']??null,$b['landmark']??null,$b['city']??'',$b['state']??null,$b['pincode']??null,$b['type']??'Home',$makeDefault?1:0]);
    $aid=(int)$pdo->lastInsertId();$g=$pdo->prepare('SELECT * FROM customer_addresses WHERE address_id=?');$g->execute([$aid]);$a=$g->fetch();
    ok(['address'=>['address_id'=>(int)$a['address_id'],'full_name'=>$a['full_name'],'mobile'=>$a['mobile'],'house'=>$a['house'],'street'=>$a['street'],'landmark'=>$a['landmark'],'city'=>$a['city'],'state'=>$a['state'],'pincode'=>$a['pincode'],'type'=>$a['type'],'is_default'=>(bool)$a['is_default']]],'Address saved');
}
if ($method==='PUT' && count($seg)===2 && $seg[0]==='addresses' && is_numeric($seg[1])) {
    $u=require_user($pdo);$b=body();$aid=(int)$seg[1];
    $g=$pdo->prepare('SELECT address_id FROM customer_addresses WHERE address_id=? AND user_id=?');$g->execute([$aid,$u['user_id']]);
    if(!$g->fetch()) fail('Address not found.',404);
    if(!empty($b['is_default'])) $pdo->prepare('UPDATE customer_addresses SET is_default=0 WHERE user_id=?')->execute([$u['user_id']]);
    $pdo->prepare('UPDATE customer_addresses SET full_name=?,mobile=?,house=?,street=?,landmark=?,city=?,state=?,pincode=?,type=?,is_default=? WHERE address_id=?')
        ->execute([$b['full_name']??'',$b['mobile']??'',$b['house']??null,$b['street']??null,$b['landmark']??null,$b['city']??'',$b['state']??null,$b['pincode']??null,$b['type']??'Home',!empty($b['is_default'])?1:0,$aid]);
    ok(null,'Address updated');
}
if ($method==='DELETE' && count($seg)===2 && $seg[0]==='addresses' && is_numeric($seg[1])) {
    $u=require_user($pdo);$pdo->prepare('UPDATE customer_addresses SET deleted_at=NOW(),is_default=0 WHERE address_id=? AND user_id=?')->execute([(int)$seg[1],$u['user_id']]);
    ok(null,'Address removed');
}
if ($method==='POST' && count($seg)===3 && $seg[0]==='addresses' && $seg[2]==='default') {
    $u=require_user($pdo);$aid=(int)$seg[1];
    $g=$pdo->prepare('SELECT address_id FROM customer_addresses WHERE address_id=? AND user_id=? AND deleted_at IS NULL');$g->execute([$aid,$u['user_id']]);
    if(!$g->fetch()) fail('Address not found.',404);
    $pdo->prepare('UPDATE customer_addresses SET is_default=0 WHERE user_id=?')->execute([$u['user_id']]);
    $pdo->prepare('UPDATE customer_addresses SET is_default=1 WHERE address_id=?')->execute([$aid]);
    ok(null,'Default address set');
}

if ($R === 'GET /') ok(['service'=>'BuyMark API','status'=>'live']);

fail('Endpoint not found: '.$R, 404);

} catch (Throwable $e) {
    // Never leak raw SQL/PHP errors to the client.
    error_log('[BuyMark API] '.$e->getMessage());
    fail('Something went wrong. Please try again.', 500);
}
