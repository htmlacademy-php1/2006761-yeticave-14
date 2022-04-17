<nav class="nav">
    <ul class="nav__list container">
    <?foreach($ArrCategories as $value):?>
        <li class="nav__item">
            <a href="all-lots.html"><?=$value['name'] ?></a>
        </li>
    <?endforeach;?>
    </ul>
</nav>
<section class="lot-item container">
    <h2><?=htmlspecialchars($ArrCatLot['lot_name']) ?></h2>
    <div class="lot-item__content">
    <div class="lot-item__left">
        <div class="lot-item__image">
        <img src="<?=htmlspecialchars($ArrCatLot['img_url']) ?>" width="730" height="548" alt="Сноуборд">
        </div>
        <p class="lot-item__category">Категория: <span><?=htmlspecialchars($ArrCatLot['cat_name']) ?></span></p>
        <p class="lot-item__description"><?=htmlspecialchars($ArrCatLot['description']) ?></p>
    </div>
    <div class="lot-item__right">
        <div class="lot-item__state">
        <div class="lot-item__timer timer  <?=oneHourTimerFinishing($ArrCatLot['finished_at']); ?>">
            <?=formatTimer($ArrCatLot['finished_at']); ?>
        </div>
        <div class="lot-item__cost-state">
            <div class="lot-item__rate">
            <span class="lot-item__amount">Текущая цена</span>
            <span class="lot-item__cost"><?=priceModify($minPrice = ((count($ArrBidUser)===0)? $ArrCatLot['start_price'] : $ArrCatLot['max_price'])) ?></span>
            </div>
            <div class="lot-item__min-cost">
            Мин. ставка <span><?=priceModify($minPrice = ($minPrice===$ArrCatLot['start_price'])? $minPrice + $ArrCatLot['step_price'] : $ArrCatLot['max_price'] + $ArrCatLot['step_price']) ?></span>
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
        <h3>История ставок (<span><?=count($ArrBidUser); ?></span>)</h3>
        <table class="history__list">
            <?foreach($ArrBidUser as $value): ?>
            <tr class="history__item">
            <td class="history__name"><?=$value['user_name'] ?></td>
            <td class="history__price"><?=priceModify($value['price']); ?></td>
            <td class="history__time"><?=$value['created_at'] ?></td>
            </tr>
            <?endforeach; ?>
        </table>
        </div>
    </div>
    </div>
</section>
