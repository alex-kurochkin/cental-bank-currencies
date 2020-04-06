<?php

/* @var $this yii\web\View */

$this->title = 'Currencies';
?>
<div class="site-index">
    <div class="body-content">
        <span class="page-label">Currencies</span>
        <hr />
        <form id="date-selector-form" action="javascript:void(0);">
            <label for="from">From:</label>
            <input type="date" name="from" id="from" required />
            <label for="to">To:</label>
            <input type="date" name="to" id="to" required />
            <input type="submit" value="Show" />
        </form>
        <div class="currencies">
            <table id="currenciesTable">
                <thead>
                <th>id</th>
                <th>Valute Id</th>
                <th>Num Code</th>
                <th>Char Code</th>
                <th>Name</th>
                <th>Nominal</th>
                <th>Value</th>
                <th>Date</th>
                </thead>
                <tbody id="currencies-list">
                <tr><td colspan="8">No data yet.<br />Please select From and To dates, and click Show button. </td></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
