<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?= htmlspecialchars($winner['name'])?></p>
<p>Ваша ставка для лота <a href="<?='lot.php?ID=' . htmlspecialchars($winner['lot_id'])?>">
<?=htmlspecialchars($winner['lot_name'])?></a> победила.</p>
<p>Перейдите по ссылке <a href="my-bets.php">мои ставки</a>,
чтобы связаться с автором объявления</p>
<small>Интернет-Аукцион "YetiCave"</small>
