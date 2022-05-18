<main>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($sqlCategories as $value) :?>
                <li class="nav__item">
                    <a href="all-lots.php?categoryName=<?=$value['symbol_code']?>"><?=$value['name'] ?></a>
                </li>
            <?php endforeach;?>
        </ul>
    </nav>
    <div class="container">
        <section class="lots">
            <div class="lots__header">
                <h2><?=empty($search) ? 'Пустой запрос' : 'Результаты поиска по запросу: '?><span>
                    <?=htmlspecialchars($search)?></span></h2>
            </div>
            <?php if (isset($searchResult)) : ?>
            <ul class="lots__list">
                <!--заполните этот список из массива с товарами-->
                <?php foreach ($searchResult as $value) : ?>
                <li class="lots__item lot">
                    <div class="lot__image">
                        <img src="<?=htmlspecialchars($value['img_url']);?>" width="350" height="260" alt="">
                    </div>
                    <div class="lot__info">
                        <span class="lot__category"><?=htmlspecialchars($value['cat_name'])?></span>
                        <h3 class="lot__title">
                        <a class="text-link" href="/lot.php?ID=<?=$value['id']?>"><?=htmlspecialchars($value['lot_name'])?></a>
                        </h3>
                        <div class="lot__state">
                            <div class="lot__rate">
                                <span class="lot__amount">Стартовая цена</span>
                                <span class="lot__cost"><?=htmlspecialchars(priceModify($value['start_price']))?></span>
                            </div>
                            <div class="lot__timer timer <?=oneHourTimerFinishing($value['finished_at'])?>">
                            <?=formatTimer($value['finished_at']); ?>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif;?>
        </section>
        <?php if (isset($pagination['countPage']) && $pagination['countPage'] > 1) :?>
        <ul class="pagination-list">
            <li class="pagination-item pagination-item-prev">
                <a href="<?='/search.php?search=' . htmlspecialchars($search) . '&page=' . $pagination['prevPage']?>">Назад</a>
            </li>
            <?php foreach ($pagination['pages'] as $value) :?>
                <?php if ($value === $pagination['currentPage']) :?>
                <li class="pagination-item pagination-item-active">
                    <a><?=$value?></a>
                </li>
                <?php else :?>
                <li class="pagination-item">
                    <a href="<?='/search.php?search=' . htmlspecialchars($search) . '&page=' . $value?>"><?=$value?></a>
                </li>
                <?php endif;?>
            <?php endforeach;?>
            <li class="pagination-item pagination-item-next">
                <a href="<?='/search.php?search=' . htmlspecialchars($search) . '&page=' . $pagination['nextPage']?>">Вперед</a>
            </li>
        </ul>
        <?php endif;?>
    </div>
</main>

