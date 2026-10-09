<form id="addform">
    <p>Название рецепта:<br><input type="text" id="txt_nazvanie" value=""></p>
    <p>Шаги приготовления (каждый шаг с новой строки):<br><textarea id="txt_shagi" rows="6" cols="50"></textarea></p>
    <input type="button" value="Добавить" onclick="funAdd()">
    <div id="addresult"></div>
</form>