<main class="container">
<section class="promo">
    <h2 class="promo__title">Нужен стафф для катки?</h2>
    <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
    <ul class="promo__list">
        <!--заполните этот список из массива категорий-->
        <?php foreach ($categories as $value): ?>
        <li class="promo__item promo__item--<?=$value['symbol_code'] ?>">
            <a class="promo__link" href="all-lots.php?categoryName=<?=$value['symbol_code']?>"><?=htmlspecialchars($value['name']); ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
<section class="lots">
    <div class="lots__header">
        <h2>Открытые лоты</h2>
    </div>
    <ul class="lots__list">
        <!--заполните этот список из массива с товарами-->
        <?php foreach ($posters as $value): ?>
        <li class="lots__item lot">
            <div class="lot__image">
                <img src="<?=$value['img_url']; ?>" width="350" height="260" alt="">
            </div>
            <div class="lot__info">
                <span class="lot__category"><?=htmlspecialchars($value['cat_name']); ?></span>
                <h3 class="lot__title">
                <a class="text-link" href="/lot.php?ID=<?=$value['id']?>"><?=$value['lot_name'];?></a>
                </h3>
                <div class="lot__state">
                    <div class="lot__rate">
                        <span class="lot__amount">Стартовая цена</span>
                        <span class="lot__cost"><?=priceModify($value['start_price']); ?></span>
                    </div>
                    <div class="lot__timer timer <?=oneHourTimerFinishing($value['finished_at']); ?>">
                    <?=formatTimer($value['finished_at']); ?>
                    </div>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
</main>
