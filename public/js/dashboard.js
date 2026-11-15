document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-food-cart]').forEach(form => {
        const picker = form.querySelector('[data-item-picker]');
        if (!picker) return;
        const quantityInput = form.querySelector('[data-item-quantity]');
        const addButton = form.querySelector('[data-add-item]');
        const list = form.querySelector('[data-cart-items]');
        const empty = form.querySelector('[data-cart-empty]');
        const fields = form.querySelector('[data-cart-fields]');
        const total = form.querySelector('[data-cart-total]');
        const submit = form.querySelector('[data-submit-order]');
        const fulfillment = form.querySelector('[data-fulfillment]');
        const clubLocation = form.querySelector('[data-club-location]');
        const deliveryFields = form.querySelector('[data-delivery-fields]');
        const orderType = form.querySelector('[data-order-type]');
        const packageFields = form.querySelector('[data-order-package-fields]');
        const foodFields = form.querySelector('[data-order-food-fields]');
        const packagePicker = form.querySelector('[data-package-picker]');
        const customerPicker = form.querySelector('[data-customer-picker]');
        const customerFilter = form.querySelector('[data-customer-filter]');
        const cart = new Map();

        const updateSubmitState = () => {
            const foodMode = orderType ? orderType.value === 'food' : true;
            const packageUnavailable = orderType && !foodMode && packagePicker?.disabled;
            const customerMissing = customerPicker && !customerPicker.value;
            submit.disabled = Boolean(packageUnavailable || customerMissing || (foodMode && cart.size === 0));
        };

        const render = () => {
            list.replaceChildren();
            fields.replaceChildren();
            let subtotal = 0;
            let currency = 'TJS';

            if (cart.size === 0) list.append(empty);
            cart.forEach((item, id) => {
                subtotal += item.price * item.quantity;
                currency = item.currency;
                const row = document.createElement('li');
                row.className = 'flex items-center justify-between gap-3 text-sm';
                const description = document.createElement('span');
                description.textContent = `${item.name} × ${item.quantity}`;
                const controls = document.createElement('span');
                controls.className = 'flex items-center gap-2';
                const amount = document.createElement('span');
                amount.className = 'font-mono text-white/70';
                amount.textContent = `${((item.price * item.quantity) / 100).toFixed(2)} ${item.currency}`;
                const minus = document.createElement('button');
                minus.type = 'button';
                minus.textContent = '−';
                minus.setAttribute('aria-label', `Remove one ${item.name}`);
                minus.addEventListener('click', () => {
                    if (item.quantity <= 1) cart.delete(id);
                    else item.quantity -= 1;
                    render();
                });
                const plus = document.createElement('button');
                plus.type = 'button';
                plus.textContent = '+';
                plus.setAttribute('aria-label', `Add one ${item.name}`);
                plus.disabled = item.quantity >= 12;
                plus.addEventListener('click', () => { item.quantity += 1; render(); });
                controls.append(minus, amount, plus);
                row.append(description, controls);
                list.append(row);

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `items[${id}]`;
                hidden.value = String(item.quantity);
                fields.append(hidden);
            });

            total.textContent = `${(subtotal / 100).toFixed(2)} ${currency}`;
            updateSubmitState();
        };

        addButton.addEventListener('click', () => {
            const option = picker.selectedOptions[0];
            if (!option?.value) {
                picker.focus();
                return;
            }
            const id = option.value;
            const count = Math.max(1, Math.min(12, Number.parseInt(quantityInput.value || '1', 10)));
            const current = cart.get(id);
            cart.set(id, {
                name: option.dataset.name,
                price: Number.parseInt(option.dataset.price, 10),
                currency: option.dataset.currency,
                quantity: Math.min(12, (current?.quantity || 0) + count),
            });
            picker.value = '';
            quantityInput.value = '1';
            render();
        });

        const updateFulfillment = () => {
            const delivery = fulfillment.value === 'delivery';
            deliveryFields.classList.toggle('hidden', !delivery);
            deliveryFields.open = delivery;
            clubLocation.classList.toggle('hidden', delivery);
            deliveryFields.querySelectorAll('[data-delivery-required]').forEach(input => { input.required = delivery; });
        };

        const updateOrderType = () => {
            if (!orderType) return;
            const foodMode = orderType.value === 'food';
            packageFields.classList.toggle('hidden', foodMode);
            foodFields.classList.toggle('hidden', !foodMode);
            if (packagePicker) packagePicker.required = !foodMode && !packagePicker.disabled;
            updateSubmitState();
        };

        if (orderType) {
            orderType.addEventListener('change', updateOrderType);
            updateOrderType();
        }
        customerPicker?.addEventListener('change', updateSubmitState);
        customerFilter?.addEventListener('input', () => {
            const filter = customerFilter.value.trim().toLocaleLowerCase();
            [...customerPicker.options].forEach(option => {
                if (option.value) option.hidden = filter !== '' && !option.dataset.search.includes(filter);
            });
            if (customerPicker.selectedOptions[0]?.hidden) customerPicker.value = '';
            updateSubmitState();
        });
        fulfillment.addEventListener('change', updateFulfillment);
        form.addEventListener('submit', event => {
            if ((!orderType || orderType.value === 'food') && cart.size === 0) {
                event.preventDefault();
                picker.focus();
            }
        });
        updateFulfillment();
        render();
    });

    const sidebar = document.querySelector('[data-ops-toggle]');
    const panel = document.getElementById('ops-sidebar');
    const scrim = document.querySelector('[data-ops-scrim]');
    if (!sidebar || !panel || !scrim) return;

    const close = () => {
        panel.classList.remove('is-open');
        scrim.classList.remove('is-visible');
        sidebar.setAttribute('aria-expanded', 'false');
    };
    sidebar.addEventListener('click', () => {
        const open = !panel.classList.contains('is-open');
        panel.classList.toggle('is-open', open);
        scrim.classList.toggle('is-visible', open);
        sidebar.setAttribute('aria-expanded', String(open));
    });
    scrim.addEventListener('click', close);
    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
});
