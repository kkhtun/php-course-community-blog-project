<?php
require_once 'core/autoload.php';
$authUser = User::auth();
if (!$authUser) {
    Helper::redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['slug'])) {
    $slug = $_GET['slug'];
    $article = DB::table('articles')->where('slug', $slug)->getOne();
    if (!$article || $article->user_id !== $authUser->id) {
        Helper::redirect('404.php');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = new Post();
    $deleted = $post->delete($_POST);
} else {
    Helper::redirect('404.php');
}

require_once 'inc/header.php';
?>
<div class="card card-dark">
    <div class="card-header">
        <h3>Delete Article</h3>
    </div>
    <div class="card-body">
        <?php if (isset($deleted) and $deleted === true) : ?>
            <div class="alert alert-success">
                Article Deleted Successfully
            </div><br>
            <a href="index.php" class="btn btn-outline-success">Back to Home</a>
            <?php
        elseif (isset($deleted) and is_array($deleted)) :
            foreach ($deleted as $e) :
            ?>
                <div class="alert alert-danger">
                    <?php echo $e ?>
                </div>
        <?php
            endforeach;
        endif;
        ?>
        <?php if (isset($article)) : ?>
            <form action="post_delete.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="" class="text-white">Are you sure you want to delete this article?</label>
                    <h2>"<?php echo $article->title ?>"</h2>
                    <input type="hidden" name="post_slug" value="<?php echo $article->slug ?>">
                </div>
                <input type="submit" value="Delete" class="btn btn-outline-danger">
                <a href="detail.php?slug=<?php echo $article->slug ?>" class="btn btn-outline-info">Cancel</a>
            </form>
        <?php endif ?>
    </div>
</div>
<?php
require_once 'inc/footer.php';
?>