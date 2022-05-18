<main>
    <nav class="nav">
        <ul class="nav__list container">
        <?php foreach ($sqlCategories as $value) :?>
            <li class="nav__item <?=$value['symbol_code'] === $sqlCatLot['symbol_code'] ? 'nav__item--current' : ''?>">
                <a href="all-lots.php?categoryName=<?=$value['symbol_code']?>"><?=$value['name']?></a>
            </li>
        <?php endforeach;?>
        </ul>
    </nav>
    <section class="lot-item container">
        <h2><?=htmlspecialchars($sqlCatLot['lot_name'])?></h2>
        <div class="lot-item__content">
        <div class="lot-item__left">
            <div class="lot-item__image">
            <img src="<?=htmlspecialchars($sqlCatLot['img_url'])?>" width="730" height="548"
                 alt="<?=htmlspecialchars($sqlCatLot['lot_name'])?>">
            </div>
            <p class="lot-item__category">Категория: <span><?=htmlspecialchars($sqlCatLot['cat_name'])?></span></p>
            <p class="lot-item__description"><?=htmlspecialchars($sqlCatLot['description'])?></p>
        </div>
        <div class="lot-item__right">
        
            <?php if ($checkAddLot && $checkActiveLot) : ?>
            <div class="lot-item__state">
            <div class="lot-item__timer timer  <?=oneHourTimerFinishing($sqlCatLot['finished_at'])?>">
                <?=formatTimer($sqlCatLot['finished_at'])?>
            </div>
            <div class="lot-item__cost-state">
                <div class="lot-item__rate">
                <span class="lot-item__amount">Текущая цена</span>
                <span class="lot-item__cost"><?=priceModify($price['currentPrice'])?></span>
                </div>
                <div class="lot-item__min-cost">
                Мин. ставка <span><?=priceModify($price['minBid'])?></span>
                </div>
            </div>
        
            <form class="lot-item__form " action="/lot.php?ID=<?=$lotId?>" method="post" autocomplete="off">
                <?php $className = !empty($errors) ? 'form__item--invalid' : '' ?>
                <p class="lot-item__form-item form__item <?=$className; ?>">
                <label for="cost">Ваша ставка</label>
                <input id="cost" type="text" name="cost" placeholder="<?=priceModify($price['minBid'])?>">
                <span class="form__error"><?=isset($errors) ? $errors : '' ?></span>
                </p>
                <button type="submit" class="button">Сделать ставку</button>
            </form>
            </div>
            <?php endif; ?>

            
            <div class="history">
            <h3>История ставок (<span><?=count($sqlBidUser)?></span>)</h3>
            <table class="history__list">
                <?php foreach ($sqlBidUser as $value) : ?>
                <tr class="history__item">
                <td class="history__name"><?=htmlspecialchars($value['user_name'])?></td>
                <td class="history__price"><?=priceModify($value['price'])?></td>
                <td class="history__time"><?=htmlspecialchars($value['time'])?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>
        </div>
        </div>
    </section>
</main>
