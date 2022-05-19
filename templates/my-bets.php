<main>
    <nav class="nav">
        <ul class="nav__list container">
        <?php foreach ($sqlCategories as $value) :?>
        <li class="nav__item">
            <a href="all-lots.php?categoryName=<?=$value['symbol_code']?>"><?=htmlspecialchars($value['name'])?></a>
        </li>
        <?php endforeach;?>
        </ul>
    </nav>
    <section class="rates container">
        <h2>Мои ставки</h2>
        <table class="rates__list">
            <?php foreach ($sqlActiveBid as $value) : ?>
            <tr class="rates__item">
                <td class="rates__info">
                <div class="rates__img">
                    <img src="<?=htmlspecialchars($value['img_url'])?>" width="54" height="40"
                         alt="<?=htmlspecialchars($value['lot_name'])?>">
                </div>
                <h3 class="rates__title"><a href="lot.php?ID=<?=$value['lot_id'];?>">
                <?=htmlspecialchars($value['lot_name'])?></a>
                </h3>
                </td>
                <td class="rates__category">
                <?=htmlspecialchars($value['cat_name'])?>
                </td>
                <td class="rates__timer">
                <div class="timer <?=oneHourTimerFinishing(htmlspecialchars($value['finished_at']))?>">
                <?=formatTimer(htmlspecialchars($value['finished_at']))?></div>
                </td>
                <td class="rates__price">
                <?=priceModify(htmlspecialchars($value['price']))?>
                </td>
                <td class="rates__time">
                <?=htmlspecialchars($value['time'])?>
                </td>
            </tr>
            <?php endforeach;?>

            <?php foreach ($sqlWinnerBid as $value) : ?>
            <tr class="rates__item rates__item--win">
                <td class="rates__info">
                <div class="rates__img">
                    <img src="<?=htmlspecialchars($value['img_url'])?>" width="54" height="40"
                         alt="<?=htmlspecialchars($value['lot_name'])?>">
                </div>
                <div>
                    <h3 class="rates__title"><a href="lot.php?ID=<?=$value['lot_id']?>">
                    <?=htmlspecialchars($value['lot_name'])?></a>
                    </h3>
                    <p><?=htmlspecialchars($value['contacts'])?></p>
                </div>
                </td>
                <td class="rates__category">
                <?=htmlspecialchars($value['cat_name'])?>
                </td>
                <td class="rates__timer">
                <div class="timer timer--win">Ставка выиграла</div>
                </td>
                <td class="rates__price">
                <?=priceModify(htmlspecialchars($value['price']))?>
                </td>
                <td class="rates__time">
                <?=htmlspecialchars($value['time'])?>
                </td>
            </tr>
            <?php endforeach;?>

            <?php foreach ($sqlFinishedBid as $value) : ?>
            <tr class="rates__item rates__item--end">
                <td class="rates__info">
                <div class="rates__img">
                    <img src="<?=htmlspecialchars($value['img_url'])?>" width="54" height="40"
                         alt="<?=htmlspecialchars($value['lot_name'])?>">
                </div>
                <h3 class="rates__title"><a href="lot.php?ID=<?=$value['lot_id']?>">
                    <?=htmlspecialchars($value['lot_name'])?></a></h3>
                </td>
                <td class="rates__category">
                <?=htmlspecialchars($value['cat_name'])?>
                </td>
                <td class="rates__timer">
                <div class="timer timer--end">Торги окончены</div>
                </td>
                <td class="rates__price">
                <?=priceModify(htmlspecialchars($value['price']))?>
                </td>
                <td class="rates__time">
                <?=htmlspecialchars($value['time'])?>
                </td>
            </tr>
            <?php endforeach;?>
        </table>
    </section>
</main>
