    <nav class="nav">
      <ul class="nav__list container">
      <?php foreach($sqlCategories as $value):?>
        <li class="nav__item">
          <a href="all-lots.html"><?=$value['name'] ?></a>
        </li>
      <?php endforeach;?>
      </ul>
    </nav>

    <?php $className = !empty($errors) ? "form--invalid" : "" ?>
    <form class="form container <?=$className; ?>" action="login.php" method="post" enctype="multipart/form-data"> <!-- form--invalid -->

      <h2>Вход</h2>
      <?php $className = isset($errors['email']) ? "form__item--invalid" : "" ?>
      <div class="form__item <?=$className; ?>"> <!-- form__item--invalid -->
        <label for="email">E-mail <sup>*</sup></label>
        <input id="email" type="text" name="email" value="<?=getPostVal('email'); ?>" placeholder="Введите e-mail">
        <span class="form__error"><?=isset($errors['email']) ? $errors['email'] : '' ?></span>
      </div>

      <?php $className = isset($errors['password']) ? "form__item--invalid" : "" ?>
      <div class="form__item form__item--last <?=$className; ?>">
        <label for="password">Пароль <sup>*</sup></label>
        <input id="password" type="password" name="password" placeholder="Введите пароль">
        <span class="form__error"><?=isset($errors['password']) ? $errors['password'] : '' ?></span>
      </div>
      <button type="submit" class="button">Войти</button>

    </form>
