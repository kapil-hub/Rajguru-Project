@extends('layouts.app')

@section('content')
<style>

.timetable-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.timetable-grid{
    width:100%;
}

.timetable-grid thead th{
    background:#2563eb;
    color:#fff;
    text-align:center;
    vertical-align:middle;
    border:none;
    padding:15px;
}

.day-column{
    background:#f8fafc;
    font-weight:700;
    width:180px;
    vertical-align:middle;
}

.slot-cell{
    min-width:130px;
    height:95px;
    cursor:pointer;
    padding:10px;
    background:white;
    transition:.25s;
}

.slot-cell:hover{
    background:#eff6ff;
}

.slot-empty{
    height:70px;
    border-radius:12px;
    border:2px dashed #cbd5e1;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:24px;
    color:#2563eb;
    transition:.25s;
}

.slot-empty:hover{
    background:#dbeafe;
    border-color:#2563eb;
    transform:scale(1.03);
}

.card-header{
    background:#fff;
}

.form-select,
.form-control{
    border-radius:12px;
}

.btn-primary{
    border-radius:12px;
}
.form-select{
    border-radius:12px;
    border:1px solid #dbe2ea;
}

.form-select:focus{
    box-shadow:none;
    border-color:#2563eb;
}

.card{
    border-radius:18px;
}

.btn-primary{
    border-radius:12px;
    font-weight:600;
}
</style>
    <livewire:admin.timetable-manager />

@endsection