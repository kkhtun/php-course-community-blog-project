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
    $updated = $post->update($_POST, $_FILES);
} else {
    Helper::redirect('404.php');
}

// To fix undefined $article error IDK
if (!isset($article)) {
    $slug = $_GET['slug'] ?? $_POST['post_slug'];
    $article = DB::table('articles')->where('slug', $slug)->getOne();
}

require_once 'inc/header.php';
?>
<div class="card card-dark">
    <div class="card-header">
        <h3>Edit Article</h3>
    </div>
    <div class="card-body">
        <form action="post_edit.php?slug=<?php echo $article->slug ?>" method="POST" enctype="multipart/form-data">
            <?php if (isset($updated) and $updated === true) : ?>
                <div class="alert alert-success">
                    Article Updated Successfully
                </div>
                <?php
            elseif (isset($updated) and is_array($updated)) :
                foreach ($updated as $e) :
                ?>
                    <div class="alert alert-danger">
                        <?php echo $e ?>
                    </div>
            <?php
                endforeach;
            endif;
            ?>
            <input type="hidden" name="post_slug" value="<?php echo $article->slug ?>">
            <div class="form-group">
                <label for="" class="text-white">Enter Title</label>
                <input type="text" name="title" class="form-control" placeholder="enter title" value="<?php echo $article->title ?>">
            </div>
            <div class="form-group">
                <label for="" class="text-white">Choose Category</label>
                <select id="" name="category_id" class="form-control">
                    <?php
                    $categories = DB::table('categories')->get();
                    foreach ($categories as $cat) : ?>
                        <option value="<?php echo $cat->id ?>" <?php echo $cat->id === $article->category_id ? 'selected' : '' ?>><?php echo $cat->name ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="form-check form-check-inline">
                <?php
                $languages = DB::table('languages')->get();
                $checkedLanguages = DB::table('article_language')->where('article_id', $article->id)->get();
                $checkedIds = array_map(function ($langObj) {
                    return $langObj->language_id;
                }, $checkedLanguages);
                foreach ($languages as $lang) : ?>
                    <span class="mr-2">
                        <input class="form-check-input" type="checkbox" name="language_id[]" value="<?php echo $lang->id ?>" <?php echo in_array($lang->id, $checkedIds) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="inlineCheckbox1"><?php echo $lang->name ?></label>
                    </span>
                <?php endforeach ?>

            </div>
            <br><br>
            <div class="form-group">
                <label for="">Choose Image</label>
                <input type="file" class="form-control" name="image">
                <img src="<?php echo $article->image ?>" style="width: 250px; border-radius: 10px;" alt="">
            </div>
            <div class="form-group">
                <label for="" class="text-white">Enter Article</label>
                <textarea name="description" class="form-control" id="" cols="30" rows="10"><?php echo $article->description ?></textarea>
            </div>
            <input type="submit" value="Update" class="btn btn-outline-warning">
        </form>
    </div>
</div>
<?php
require_once 'inc/footer.php';
?>