    <nav class="nav">
      <ul class="nav__list container">
        <?php foreach($sqlCategories as $value):?>
        <li class="nav__item">
          <a href="all-lots.html"><?=$value['name'] ?></a>
        </li>
      <?php endforeach;?>
      </ul>
    </nav>
    <?php $classname = !empty($errors) ? "form--invalid" : "" ?>
    <form class="form container <?=$classname; ?>" action="sign-up.php" method="post" autocomplete="off"> <!-- form
    --invalid -->
      <h2>Регистрация нового аккаунта</h2>
      <?php $classname = isset($errors['email']) ? "form__item--invalid" : "" ?>
      <div class="form__item <?=$classname; ?>"> <!-- form__item--invalid -->
        <label for="email">E-mail <sup>*</sup></label>
        <input id="email" type="text" name="email" value="<?=getPostVal('email'); ?>" placeholder="Введите e-mail">
        <span class="form__error"><?=isset($errors['email']) ? $errors['email'] : '' ?></span>
      </div>
      <?php $classname = isset($errors['password']) ? "form__item--invalid" : "" ?>
      <div class="form__item <?=$classname; ?>">
        <label for="password">Пароль <sup>*</sup></label>
        <input id="password" type="password" name="password" placeholder="Введите пароль">
        <span class="form__error"><?=isset($errors['password']) ? $errors['password'] : '' ?></span>
      </div>
      <?php $classname = isset($errors['name']) ? "form__item--invalid" : "" ?>
      <div class="form__item <?=$classname; ?>">
        <label for="name">Имя <sup>*</sup></label>
        <input id="name" type="text" name="name" value="<?=getPostVal('name'); ?>" placeholder="Введите имя">
        <span class="form__error"><?=isset($errors['name']) ? $errors['name'] : '' ?></span>
      </div>
      <?php $classname = isset($errors['contacts']) ? "form__item--invalid" : "" ?>
      <div class="form__item <?=$classname; ?>">
        <label for="message">Контактные данные <sup>*</sup></label>
        <textarea id="message" name="contacts" placeholder="Напишите как с вами связаться"><?=getPostVal('contacts'); ?></textarea>
        <span class="form__error"><?=isset($errors['contacts']) ? $errors['contacts'] : '' ?></span>
      </div>
      <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
      <button type="submit" class="button">Зарегистрироваться</button>
      <a class="text-link" href="#">Уже есть аккаунт</a>
    </form>
