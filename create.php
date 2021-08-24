<?php
require_once 'core/autoload.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = new Post();
    $created = $post->create($_POST, $_FILES);
}

require_once 'inc/header.php';
?>
<div class="card card-dark">
    <div class="card-header">
        <h3>Create New Article</h3>
    </div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <?php if (isset($created) and $created === true) : ?>
                <div class="alert alert-success">
                    Article Created Successfully
                </div>
                <?php
            elseif (isset($created) and is_array($created)) :
                foreach ($created as $e) :
                ?>
                    <div class="alert alert-danger">
                        <?php echo $e ?>
                    </div>
            <?php
                endforeach;
            endif;
            ?>
            <div class="form-group">
                <label for="" class="text-white">Enter Title</label>
                <input type="text" name="title" class="form-control" placeholder="enter title">
            </div>
            <div class="form-group">
                <label for="" class="text-white">Choose Category</label>
                <select id="" name="category_id" class="form-control">
                    <?php
                    $categories = DB::table('categories')->get();
                    foreach ($categories as $cat) : ?>
                        <option value="<?php echo $cat->id ?>"><?php echo $cat->name ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="form-check form-check-inline">
                <?php
                $languages = DB::table('languages')->get();
                foreach ($languages as $lang) : ?>
                    <span class="mr-2">
                        <input class="form-check-input" type="checkbox" name="language_id[]" value="<?php echo $lang->id ?>">
                        <label class="form-check-label" for="inlineCheckbox1"><?php echo $lang->name ?></label>
                    </span>
                <?php endforeach ?>

            </div>
            <br><br>
            <div class="form-group">
                <label for="">Choose Image</label>
                <input type="file" class="form-control" name="image">
            </div>
            <div class="form-group">
                <label for="" class="text-white">Enter Articles</label>
                <textarea name="description" class="form-control" id="" cols="30" rows="10"></textarea>
            </div>
            <input type="submit" value="Create" class="btn  btn-outline-warning">
        </form>
    </div>
</div>
<?php
require_once 'inc/footer.php';
?>