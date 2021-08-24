<?php
require_once 'core/autoload.php';

if (isset($_GET['category'])) {
    $slug = $_GET['category'];
    $articles = Post::articlesByCategory($slug);
} else if (isset($_GET['language'])) {
    $slug = $_GET['language'];
    $articles = Post::articlesByLanguage($slug);
} else if (isset($_GET['search']) && strlen($_GET['search']) > 0) {
    $search = $_GET['search'];
    $articles = Post::search($search);
} else if (isset($_GET['author'])) {
    $slug = $_GET['author'];
    $articles = Post::articleByAuthor($slug);
} else {
    $articles = Post::all();
}

if ($articles === false) {
    Helper::redirect('404.php');
}
require_once 'inc/header.php';
?>
<div class="card card-dark">
    <div class="card-body">
        <a href="<?php echo $articles['prev_page'] ?>" class="btn btn-danger">Prev Posts</a>
        <a href="<?php echo $articles['next_page'] ?>" class="btn btn-danger float-right">Next Posts</a>
    </div>
</div>
<div class="card card-dark">
    <div class="card-body">
        <div class="row">
            <!-- Loop this -->
            <?php
            foreach ($articles['data'] as $art) :
            ?>
                <div class="col-md-4 mt-2">
                    <div class="card" style="width: 18rem;">
                        <img class="card-img-top" src="<?php echo $art->image ?>" alt="Card image cap">
                        <div class="card-body">
                            <h5 class="text-dark"><?php echo $art->title ?></h5>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <?php
                                $authUser = User::auth();
                                ?>
                                <div class="col-md-4 text-center like" user_id="<?php echo $authUser ? $authUser->id : 0 ?>" article_id="<?php echo $art->id ?>">
                                    <i class="fas fa-heart text-warning">
                                    </i>
                                    <small class="text-muted"><?php echo $art->like_count ?></small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="far fa-comment text-dark"></i>
                                    <small class="text-muted"><?php echo $art->comment_count ?></small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <a href="detail.php?slug=<?php echo $art->slug ?>" class="badge badge-warning p-1">View</a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach ?>

        </div>
    </div>
</div>
<div class="card card-dark">
    <div class="card-body">
        <div class="text-muted text-center">Page <?php echo $articles['current_page_no'] ?> of <?php echo $articles['total_pages'] ?></div>
    </div>
</div>
<?php require_once 'inc/footer.php' ?>
<script>
    // Like event in assets/helper.js
    var likes = document.querySelectorAll('.like');
    likes.forEach((like) => addLikeEvent(like));
</script>