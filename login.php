<?php
require_once 'core/autoload.php';
if (User::auth()) {
    Helper::redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $res = $user->login($_POST);
    if ($res === true) {
        Helper::redirect('index.php');
    }
}
require_once 'inc/header.php'; ?>

<div class="card card-dark">
    <div class="card-header bg-warning">
        <h3>Login</h3>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <?php
            if (isset($res) && is_array($res)) :
                foreach ($res as $e) :
            ?>
                    <div class="alert alert-danger">
                        <?php echo $e ?>
                    </div>
            <?php
                endforeach;
            endif;
            ?>
            <div class="form-group">
                <label for="" class="text-white">Enter Email</label>
                <input type="text" class="form-control" name="email" placeholder="enter email">
            </div>
            <div class="form-group">
                <label for="" class="text-white">Enter Password</label>
                <input type="password" class="form-control" name="password" placeholder="enter password">
            </div>
            <input type="submit" value="Login" class="btn btn-outline-warning">
        </form>
    </div>
</div>
<?php
require_once 'inc/footer.php'; ?>