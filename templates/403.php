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
    <section class="lot-item container">
        <h2>HTTP-код ответа 403</h2>
    </section>
</main>
