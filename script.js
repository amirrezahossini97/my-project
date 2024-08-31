document.getElementById('add-order-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('name').value;
    const phone = document.getElementById('phone').value;
    const entry = document.getElementById('entry').value;
    const notes = document.getElementById('notes').value;
    
    const orderList = document.getElementById('order-list');
    const row = document.createElement('tr');
    
    row.innerHTML = `
        <td>${orderList.children.length + 1}</td>
        <td>${name}</td>
        <td>${phone}</td>
        <td>${entry}</td>
        <td>${notes}</td>
    `;
    
    orderList.appendChild(row);
    
    document.getElementById('add-order-form').reset();
});
