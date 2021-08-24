<?php
require_once 'core/autoload.php';

if (isset($_GET['slug']) && DB::table('articles')->where('slug', $_GET['slug'])->getOne()) {
    $slug = $_GET['slug'];
    $article = Post::detail($slug);
} else {
    Helper::redirect('404.php');
}
require_once 'inc/header.php';
?>
<div class="card card-dark">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-dark">
                    <div class="card-body">
                        <div class="row">
                            <!-- icons -->
                            <div class="col-md-4">
                                <div class="row">
                                    <?php
                                    $user = User::auth();
                                    $user_id = $user ? $user->id : 0;
                                    $article_id = $article->id;
                                    ?>
                                    <div class="col-md-3 text-center" id="like" user_id="<?php echo $user_id ?>" article_id="<?php echo $article_id ?>">
                                        <i class="fas fa-heart text-warning">
                                        </i>
                                        <small class="text-muted like-count"><?php echo $article->like_count ?></small>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <i class="far fa-comment text-dark"></i>
                                        <small class="text-muted comment-count"><?php echo $article->comment_count ?></small>
                                    </div>
                                    <?php if ($user_id === $article->user_id) : ?>
                                        <div class="col-md-3 text-center">
                                            <a href="post_edit.php?slug=<?php echo $article->slug ?>" class="badge badge-info p-2">Edit</a>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <a href="post_delete.php?slug=<?php echo $article->slug ?>" class="badge badge-danger p-2">Delete</a>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                            <!-- Icons -->

                            <!-- Category -->
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="offset-md-4 col-md-4">
                                        <a href="" class="badge badge-primary"><?php echo $article->category->name ?></a>
                                    </div>
                                </div>
                            </div>
                            <!-- Category -->


                            <!-- Language -->
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="offset-md-4 col-md-4">
                                        <?php foreach ($article->languages as $lang) : ?>
                                            <a href="" class="badge badge-success"><?php echo $lang->name ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Language -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="col-md-12">
            <h3><?php echo $article->title ?></h3>
            <p>
                <?php echo $article->description ?>
            </p>
        </div>

        <!-- Create Comments -->
        <div class="card card-dark">
            <div class="card-body">
                <form action="" method="POST" id="comment-form">
                    <input type="text" name="comment" class="form-control mb-2" id="comment" placeholder="enter comment">
                    <input type="submit" value="Comment" class="btn btn-outline-warning float-right">
                </form>
            </div>
        </div>
        <!-- Comments -->
        <div class="card card-dark">
            <div class="card-header">
                <h4>Comments</h4>
            </div>
            <div class="card-body">
                <!-- Loop Comment -->
                <div id="comment-list">
                    <?php foreach ($article->comments as $comment) :
                        $comment_user = DB::table('users')->where('id', $comment->user_id)->getOne();
                    ?>
                        <div class="card-dark mt-1">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-1">
                                        <img src="<?php echo $comment_user->image ?>" style="width:50px;border-radius:50%" alt="">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center">
                                        <?php echo $comment_user->name ?>
                                    </div>
                                </div>
                                <hr>
                                <p><?php echo $comment->comment ?></p>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>

            </div>
        </div>
    </div>
</div>
<?php
require_once 'inc/footer.php';
?>

<script>
    // Like
    var like = document.querySelector('#like')
    addLikeEvent(like);

    // Comment
    var commentForm = document.getElementById('comment-form');
    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var data = new FormData();
        data.append("comment", document.getElementById("comment").value)
        data.append("article_id", <?php echo $article->id ?>)
        data.append("user_id", <?php echo $user_id ?>)

        fetch(`api.php`, {
                method: 'POST',
                body: data
            })
            .then((res) => res.json())
            .then((data) => {
                var commentList = document.getElementById("comment-list");
                commentList.innerHTML = data.html;
                var commentCount = document.querySelector(".comment-count");
                commentCount.innerHTML = data.count;
                document.getElementById("comment").value = "";
            })
    })
</script>