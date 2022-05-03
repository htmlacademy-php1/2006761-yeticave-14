<nav class="nav">
    <ul class="nav__list container">
    <?php foreach($sqlCategories as $value):?>
        <li class="nav__item">
            <a href="all-lots.html"><?=$value['name'] ?></a>
        </li>
    <?php endforeach;?>
    </ul>
</nav>
<section class="lot-item container">
    <h2><?=htmlspecialchars($sqlCatLot['lot_name']) ?></h2>
    <div class="lot-item__content">
    <div class="lot-item__left">
        <div class="lot-item__image">
        <img src="<?=htmlspecialchars($sqlCatLot['img_url']) ?>" width="730" height="548" alt="Сноуборд">
        </div>
        <p class="lot-item__category">Категория: <span><?=htmlspecialchars($sqlCatLot['cat_name']) ?></span></p>
        <p class="lot-item__description"><?=htmlspecialchars($sqlCatLot['description']) ?></p>
    </div>
    <div class="lot-item__right">
        <?php if (!empty($userName)): ?>
        <div class="lot-item__state">
        <div class="lot-item__timer timer  <?=oneHourTimerFinishing($sqlCatLot['finished_at']); ?>">
            <?=formatTimer($sqlCatLot['finished_at']); ?>
        </div>
        <div class="lot-item__cost-state">
            <div class="lot-item__rate">
            <span class="lot-item__amount">Текущая цена</span>
            <span class="lot-item__cost"><?=priceModify($minPrice = ((count($sqlBidUser)===0)? $sqlCatLot['start_price'] : $sqlCatLot['max_price'])) ?></span>
            </div>
            <div class="lot-item__min-cost">
            Мин. ставка <span><?=priceModify($minPrice = ($minPrice===$sqlCatLot['start_price'])? $minPrice + $sqlCatLot['step_price'] : $sqlCatLot['max_price'] + $sqlCatLot['step_price']) ?></span>
            </div>
        </div>
        <form class="lot-item__form" action="https://echo.htmlacademy.ru" method="post" autocomplete="off">
            <p class="lot-item__form-item form__item form__item--invalid">
            <label for="cost">Ваша ставка</label>
            <input id="cost" type="text" name="cost" placeholder="<?=priceModify($minPrice); ?>">
            <span class="form__error">Введите наименование лота</span>
            </p>
            <button type="submit" class="button">Сделать ставку</button>
        </form>
        </div>
        <div class="history">
        <h3>История ставок (<span><?=count($sqlBidUser); ?></span>)</h3>
        <table class="history__list">
            <?php foreach($sqlBidUser as $value): ?>
            <tr class="history__item">
            <td class="history__name"><?=$value['user_name'] ?></td>
            <td class="history__price"><?=priceModify($value['price']); ?></td>
            <td class="history__time"><?=$value['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <?php endif; ?>
    </div>
    </div>
</section>
