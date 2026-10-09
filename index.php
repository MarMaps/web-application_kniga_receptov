<!DOCTYPE HTML>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Книга рецептов</title>
        <script src='main.js' defer></script>
    </head>
    <body>
        <h1>Книга рецептов</h1>
        Сортировать по:
        <input name='btn_sort_like' type='button' onclick="funSort('like')" value='Лайкам'>
        <input name='btn_sort_dislike' type='button' onclick="funSort('dislike')" value='Дизлайкам'>
        <br>
        <input name='btn1' type='button' onclick="funShowAdd()" value='Добавить рецепт'>
        <div id="addblock"></div>
        <div id="result"></div>
        <table border="1" id="id5" cellpadding="5">
            <tr>
                <th>id</th>
                <th >Название</th>
                <th >Шаги приготовления</th>
                <th>Лайки</th>
                <th>Дизлайки</th>
                <th>Оценка</th>
            </tr>
        </table>
    </body>
</html>