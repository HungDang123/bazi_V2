.traits-row {
    width: 100%;
    border-collapse: separate;
    border-spacing: 4mm 0;
    table-layout: fixed;
    margin-bottom: 6mm;
}

.traits-row > tbody > tr > td.traits-col {
    width: 50%;
    vertical-align: top;
    padding: 0;
}

.trait-box {
    width: 100%;
    border: 0.5mm solid;
    border-radius: 3mm;
    overflow: visible;
    background: #FFFFFF;
    box-sizing: border-box;
}

.trait-box.tich-cuc { border-color: #4CAF50; }
.trait-box.tieu-cuc { border-color: #C62828; }

.trait-pill-wrap {
    text-align: center;
    padding: 2.5mm 2mm 2mm;
}

.trait-pill-img {
    display: inline-block;
}

.trait-pill {
    display: inline-block;
    padding: 1.4mm 8mm;
    border-radius: 99mm;
    color: #FFFFFF;
    font-weight: bold;
    font-size: 12px;
    line-height: 120%;
}

.trait-box.tich-cuc .trait-pill { background: #4CAF50; }
.trait-box.tieu-cuc .trait-pill { background: #8B1A1A; }

.trait-body-cell {
    color: #1A1A1A;
    font-size: 14px;
    line-height: 140%;
    padding: 0 4mm 4mm;
    text-align: justify;
}

.trait-body-cell p {
    margin-bottom: 2.5mm;
    text-align: justify;
}

.trait-body-cell p:last-child { margin-bottom: 0; }
