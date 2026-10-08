function fun1() {
    fetch('handler5.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
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

            const select = document.getElementById('id4');
            select.innerHTML = '';

            result.data.forEach(student => {
                const option = document.createElement('option');
                option.value = student.id;
                option.textContent = student.fio;
                select.appendChild(option);
            });
        } 
        
    })
    .catch(error => {
        console.error('Ошибка:', error);
        document.getElementById('result').innerHTML = 'Произошла ошибка сети или JS.';
    });
}
