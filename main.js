// поставить лайк ИЛИ дизлайк одному рецепту (можно сколько угодно раз)
function funVote(id, type) {
    fetch('handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'vote',
            id: id,
            type: type
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('result').innerHTML = '<b>Ответ от сервера:</b> ' + result.message;
        } else {
            document.getElementById('result').innerHTML = 'Ошибка: ' + result.message;
        }
        fun1();
    })
    .catch(error => {
        console.error('Ошибка:', error);
        document.getElementById('result').innerHTML = 'Произошла ошибка сети или JS.';
    });
}

// текущая сортировка: 'id', 'like' или 'dislike'
let sortBy = 'id';

// сортировка по лайкам/дизлайкам по убыванию
function funSort(type) {
    sortBy = type;
    fun1();
}

function fun1() {
    fetch('handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'list',
            sort: sortBy
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Сервер вернул статус: ' + response.status);
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            document.getElementById('result').innerHTML = '<b>Ответ от сервера:</b> ' + result.message;

            const table = document.getElementById('id5');
            // удаляем все строки, кроме заголовка
            while (table.rows.length > 1) {
                table.deleteRow(1);
            }

            result.data.forEach(recept => {
                const tr = document.createElement('tr');

                const id = document.createElement('td');
                id.textContent = recept.id;
                tr.appendChild(id);

                const nazvanie = document.createElement('td');
                nazvanie.textContent = recept.nazvanie;
                tr.appendChild(nazvanie);

                const shagi = document.createElement('td');
                // переводим \n в <br> для отображения шагов
                shagi.innerHTML = String(recept.shagi_prigotovleniya).replace(/\n/g, '<br>');
                tr.appendChild(shagi);

                const likes = document.createElement('td');
                likes.textContent = recept.likes;
                tr.appendChild(likes);

                const dislikes = document.createElement('td');
                dislikes.textContent = recept.dislikes;
                tr.appendChild(dislikes);

                const golosa = document.createElement('td');

                const btnLike = document.createElement('button');
                btnLike.type = 'button';
                btnLike.textContent = 'Лайк';
                btnLike.onclick = () => funVote(recept.id, 'like');

                const btnDislike = document.createElement('button');
                btnDislike.type = 'button';
                btnDislike.textContent = 'Дизлайк';
                btnDislike.onclick = () => funVote(recept.id, 'dislike');

                golosa.appendChild(btnLike);
                golosa.appendChild(document.createTextNode(' '));
                golosa.appendChild(btnDislike);

                tr.appendChild(golosa);

                table.appendChild(tr);
            });
        } else {
            document.getElementById('result').innerHTML = 'Ошибка: ' + result.message;
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        document.getElementById('result').innerHTML = 'Произошла ошибка сети или JS.';
    });
}

window.addEventListener('load', fun1);

// показать/скрыть форму добавления
function funShowAdd() {
    const block = document.getElementById('addblock');

    if (block.innerHTML !== '') {
        block.innerHTML = '';
        return;
    }

    fetch('add.php')
        .then(response => response.text())
        .then(html => {
            block.innerHTML = html;
        })
        .catch(error => {
            console.error('Ошибка:', error);
            block.innerHTML = 'Не удалось загрузить форму.';
        });
}

// добавить рецепт в базу
function funAdd() {
    const nazvanie = document.getElementById('txt_nazvanie').value;
    const shagi = document.getElementById('txt_shagi').value;
    const addresult = document.getElementById('addresult');

    fetch('handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'insert',
            nazvanie: nazvanie,
            shagi_prigotovleniya: shagi
        })
    })
    .then(response => response.json())
    .then(result => {
        addresult.innerHTML = result.message;

        if (result.success) {
            document.getElementById('txt_nazvanie').value = '';
            document.getElementById('txt_shagi').value = '';
            fun1(); // обновляем таблицу
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        addresult.innerHTML = 'Произошла ошибка сети или JS.';
    });
}