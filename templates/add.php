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
    <form class="form form--add-lot container <?=$className; ?>" action="add.php" method="POST" enctype="multipart/form-data"> <!-- form--invalid -->
      <h2>Добавление лота</h2>
      <div class="form__container-two">
      <?php $className = isset($errors['name']) ? "form__item--invalid" : "" ?>
        <div class="form__item <?=$className; ?>"> <!-- form__item--invalid -->
          <label for="lot-name">Наименование <sup>*</sup></label>
          <input id="lot-name" type="text" name="name" placeholder="Введите наименование лота" value="<?=getPostVal('name'); ?>">
          <span class="form__error"><?=isset($errors['name']) ? $errors['name'] : '' ?></span>
        </div>
         <?php $className = isset($errors['category_id']) ? "form__item--invalid" : "" ?>
        <div class="form__item <?=$className; ?>">
          <label for="category">Категория <sup>*</sup></label>
          <select id="category" name="category_id">
            <option>Выберите категорию</option>
            <?php foreach ($sqlCategories as $value) : ?>
            <option value="<?=$value['id']; ?>"
                <?php if ($value['id'] === getPostVal('category_id')): ?>selected<?php endif; ?>>
                <?=$value['name'];?></option>
            <?php endforeach; ?>
          </select>
          <span class="form__error"><?=isset($errors['category_id']) ? $errors['category_id'] : '' ?></span>
        </div>
      </div>
       <?php $className = isset($errors['description']) ? "form__item--invalid" : "" ?>
      <div class="form__item form__item--wide <?=$className; ?>">
        <label for="message">Описание <sup>*</sup></label>
        <textarea id="message" name="description" placeholder="Напишите описание лота"><?=getPostVal('description'); ?></textarea>
        <span class="form__error"><?=isset($errors['description']) ? $errors['description'] : '' ?></span>
      </div>
      <?php $className = isset($errors['img_url']) ? "form__item--invalid" : "" ?>
      <div class="form__item form__item--file <?=$className; ?>">
        <label>Изображение <sup>*</sup></label>
        <div class="form__input-file">
          <input class="visually-hidden" type="file" id="lot-img" name="img_url" value="">
          <label for="lot-img">
            Добавить
          </label>
        </div>
        <span class="form__error"><?=isset($errors['img_url']) ? $errors['img_url'] : '' ?></span>
      </div>
      <div class="form__container-three">
      <?php $className = isset($errors['start_price']) ? "form__item--invalid" : "" ?>
        <div class="form__item form__item--small <?=$className; ?>">
          <label for="lot-rate">Начальная цена <sup>*</sup></label>
          <input id="lot-rate" type="text" name="start_price" value="<?=getPostVal('start_price'); ?>" placeholder="0">
          <span class="form__error"><?=isset($errors['start_price']) ? $errors['start_price'] : '' ?></span>
        </div>
        <?php $className = isset($errors['step_price']) ? "form__item--invalid" : "" ?>
        <div class="form__item form__item--small <?=$className; ?>">
          <label for="lot-step">Шаг ставки <sup>*</sup></label>
          <input id="lot-step" type="text" name="step_price" value="<?=getPostVal('step_price'); ?>" placeholder="0">
          <span class="form__error"><?=isset($errors['step_price']) ? $errors['step_price'] : '' ?></span>
        </div>
        <?php $className = isset($errors['finished_at']) ? "form__item--invalid" : "" ?>
        <div class="form__item <?=$className; ?>"">
          <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
          <input class="form__input-date" id="lot-date" type="text" name="finished_at" value="<?=getPostVal('finished_at'); ?>" placeholder="Введите дату в формате ГГГГ-ММ-ДД">
          <span class="form__error"><?=isset($errors['finished_at']) ? $errors['finished_at'] : '' ?></span>
        </div>
      </div>
      <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
      <button type="submit" class="button">Добавить лот</button>
    </form>
