<?php
require_once 'core/autoload.php';
switch ($_SERVER['REQUEST_METHOD']) {
    case "GET":
        $request = $_GET;
        break;
    case "POST":
        $request = $_POST;
        break;
}
$authUser = User::auth();
if (
    !$authUser ||
    !isset($request['user_id']) ||
    !isset($request['article_id'])
    || $authUser->id !== $request['user_id']
) {
    die();
}

if (isset($request['like'])) {
    $user_id = $request['user_id'];
    $article_id = $request['article_id'];

    $liked = DB::table('article_likes')->where('user_id', $user_id)->andWhere('article_id', $article_id)->getOne();
    $status = "";
    if ($liked) {
        DB::delete('article_likes', $liked->id);
        $status = "unliked";
    } else {
        $user = DB::create('article_likes', [
            "user_id" => $user_id,
            "article_id" => $article_id
        ]);
        if ($user) {
            $status = "liked";
        }
    }
    echo json_encode(array(
        "status" => $status,
        "count" => DB::table('article_likes')->where('article_id', $article_id)->count()
    ));
} elseif (isset($request['comment'])) {
    $user_id = $request['user_id'];
    $article_id = $request['article_id'];
    $comment = $request['comment'];

    $createdComment = DB::create('article_comments', [
        "user_id" => $user_id,
        "article_id" => $article_id,
        "comment" => Helper::filter($comment)
    ]);

    if ($createdComment) {
        $allComments = DB::table('article_comments')->where('article_id', $article_id)->orderBy('id', 'DESC')->get();
        $commentCount = count($allComments);
        $html = "";
        foreach ($allComments as $comment) {
            $commentUser = DB::table('users')->where('id', $comment->user_id)->getOne();
            $html .= "
            <div class='card-dark mt-1'>
                        <div class='card-body'>
                            <div class='row'>
                                <div class='col-md-1'>
                                    <img src='{$commentUser->image}' style='width:50px;border-radius:50%'>
                                </div>
                                <div class='col-md-4 d-flex align-items-center'>
                                    {$commentUser->name}
                                </div>
                            </div>
                            <hr>
                            <p>{$comment->comment}</p>
                        </div>
                    </div>";
        }
        echo json_encode(array(
            "html" => $html,
            "count" => $commentCount
        ));
    }
}
