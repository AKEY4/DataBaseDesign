        function fetchShots() {
            const shooter = document.getElementById("shooter").value;
            window.location.href = `?shooter=${shooter}`;
        }

        function sortTable(column) {
            const urlParams = new URLSearchParams(window.location.search);
            let order = urlParams.get('order') === 'ASC' ? 'DESC' : 'ASC';
            urlParams.set('sort', column);
            urlParams.set('order', order);
            window.location.search = urlParams.toString();
        }