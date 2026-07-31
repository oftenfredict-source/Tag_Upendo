<script>
(function () {
    var maxChildAge = {{ \App\Models\Member::MAX_CHILD_AGE }};
    var yearsLabel = @json(__(':count years', ['count' => '__AGE__']));
    var yearLabel = @json(__(':count year', ['count' => '__AGE__']));
    var emptyLabel = @json(__('—'));
    var tooOldLabel = @json(__('Child must be 18 years or younger'));

    function ageFromDate(dateStr) {
        if (!dateStr) return null;
        var dob = new Date(dateStr + 'T00:00:00');
        if (isNaN(dob.getTime())) return null;
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        return age >= 0 ? age : null;
    }

    function formatAge(age) {
        if (age === null || age === undefined || isNaN(age)) return emptyLabel;
        if (age > maxChildAge) return tooOldLabel;
        if (age === 1) return yearLabel.replace('__AGE__', '1');
        return yearsLabel.replace('__AGE__', String(age));
    }

    function updateRowAge(row) {
        var input = row.querySelector('.child-dob-input');
        var display = row.querySelector('.child-age-display');
        if (!display) return;
        var age = ageFromDate(input ? input.value : '');
        display.textContent = formatAge(age);
        display.classList.toggle('text-danger', age !== null && age > maxChildAge);
        display.classList.toggle('text-primary', age === null || age <= maxChildAge);
    }

    function reindexChildRows(container) {
        var rows = container.querySelectorAll('[data-child-row]');
        rows.forEach(function (row, index) {
            var numEl = row.querySelector('.child-row-num');
            if (numEl) numEl.textContent = String(index + 1);

            row.querySelectorAll('[name^="children["]').forEach(function (input) {
                input.name = input.name.replace(/children\[\d+\]/, 'children[' + index + ']');
            });

            var removeBtn = row.querySelector('.remove-child-row');
            if (removeBtn) removeBtn.hidden = rows.length === 1;

            updateRowAge(row);
        });
    }

    function initChildrenRegistration() {
        var hasYes = document.getElementById('hasChildrenYes');
        var hasNo = document.getElementById('hasChildrenNo');
        var listWrap = document.getElementById('childrenListWrap');
        var rowsContainer = document.getElementById('childrenRows');
        var addBtn = document.getElementById('addChildRowBtn');
        var template = document.getElementById('childRowTemplate');

        if (!rowsContainer || !template) return;

        function toggleChildrenList() {
            if (!listWrap) return;
            listWrap.hidden = !(hasYes && hasYes.checked);
        }

        if (hasYes) hasYes.addEventListener('change', toggleChildrenList);
        if (hasNo) hasNo.addEventListener('change', toggleChildrenList);

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                var index = rowsContainer.querySelectorAll('[data-child-row]').length;
                var html = template.innerHTML.replace(/__INDEX__/g, String(index));
                var wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                var row = wrapper.firstElementChild;
                rowsContainer.appendChild(row);
                reindexChildRows(rowsContainer);
            });
        }

        rowsContainer.addEventListener('click', function (e) {
            var btn = e.target.closest('.remove-child-row');
            if (!btn) return;
            var row = btn.closest('[data-child-row]');
            if (!row) return;
            var rows = rowsContainer.querySelectorAll('[data-child-row]');
            if (rows.length <= 1) return;
            row.remove();
            reindexChildRows(rowsContainer);
        });

        rowsContainer.addEventListener('change', function (e) {
            if (e.target.classList.contains('child-dob-input')) {
                var row = e.target.closest('[data-child-row]');
                if (row) updateRowAge(row);
            }
        });

        reindexChildRows(rowsContainer);
        toggleChildrenList();
    }

    window.buildChildrenSummary = function (form, labels) {
        var card = document.getElementById('summaryChildrenCard');
        var tbody = document.getElementById('summaryChildrenBody');
        if (!card || !tbody) return;

        var hasChildren = form.querySelector('input[name="has_children"]:checked');
        if (!hasChildren || hasChildren.value !== '1') {
            card.hidden = true;
            tbody.innerHTML = '';
            return;
        }

        var rows = form.querySelectorAll('#childrenRows [data-child-row]');
        var html = '';
        var count = 0;

        rows.forEach(function (row) {
            var nameInput = row.querySelector('.child-name-input');
            var name = nameInput ? nameInput.value.trim() : '';
            if (!name) return;

            var genderSelect = row.querySelector('select[name*="[gender]"]');
            var gender = genderSelect ? genderSelect.value : '';
            var dobInput = row.querySelector('.child-dob-input');
            var dob = dobInput ? dobInput.value : '';
            var age = formatAge(ageFromDate(dob));

            html += '<tr>';
            html += '<td>' + name + '</td>';
            html += '<td>' + (labels.gender[gender] || labels.empty) + '</td>';
            html += '<td>' + (dob || labels.empty) + '</td>';
            html += '<td>' + age + '</td>';
            html += '</tr>';
            count++;
        });

        if (count === 0) {
            card.hidden = true;
            tbody.innerHTML = '';
            return;
        }

        card.hidden = false;
        tbody.innerHTML = html;
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChildrenRegistration);
    } else {
        initChildrenRegistration();
    }
})();
</script>
