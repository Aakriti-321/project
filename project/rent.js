let price = 0;

function openForm(id, perDayPrice) {
    document.getElementById('bookingForm').style.display = "flex";
    document.getElementById('vehicle_id').value = id;

    price = perDayPrice;

    document.getElementById('start_date').value = "";
    document.getElementById('end_date').value = "";
    document.getElementById('num_days_display').textContent = "0";
    document.getElementById('total_price').textContent = "0";
}

function closeForm() {
    document.getElementById('bookingForm').style.display = "none";
}

function calculateTotal() {
    let s = document.getElementById('start_date').value;
    let e = document.getElementById('end_date').value;

    if (s !== "" && e !== "") {
        let start = new Date(s);
        let end = new Date(e);

        let diff = end - start;
        let days = diff / (1000 * 60 * 60 * 24) + 1;

        if (days > 0) {
            let total = days * price;

            document.getElementById('num_days_display').textContent = days;
            document.getElementById('total_price').textContent = total;

            document.getElementById('num_days_input').value = days;
            document.getElementById('total_price_input').value = total;
        }
    }
}

document.getElementById("searchInput").oninput = function() {
    let text = this.value.toLowerCase();
    let cards = document.querySelectorAll(".vehicle-card");

    cards.forEach(card => {
        card.style.display = card.innerText.toLowerCase().includes(text) ? "" : "none";
    });
};
