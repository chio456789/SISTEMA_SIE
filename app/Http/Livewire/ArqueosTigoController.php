<?php

namespace App\Http\Livewire;

use App\Models\Transaccion;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class ArqueosTigoController extends Component
{
    public $fromDate, $toDate, $userid, $total, $transaccions, $details, $importe;

    public function mount()
    {
        $this->fromDate = null;
        $this->toDate = null;
        $this->userid = 0;
        $this->total = 0;
        $this->transaccions = [];
        $this->details = [];
    }

    public function render()
    {
        return view('livewire.arqueos_tigo.component', [
            'users' => User::orderBy('name', 'asc')->get()
        ])->extends('layouts.theme.app')
            ->section('content');
    }

    public function Consultar()
    {
        $fi = Carbon::parse($this->fromDate)->format('Y-m-d') . ' 00:00:00';
        $ff = Carbon::parse($this->toDate)->format('Y-m-d') . ' 23:59:59';

        $this->transaccions = Transaccion::join('users as u', 'transaccions.user_id', 'u.id')
            ->select('u.name as nombre', 'transaccions.*')
            ->whereBetween('transaccions.created_at', [$fi, $ff])
            ->where('transaccions.user_id', $this->userid)
            ->orderBy('transaccions.id', 'desc')
            ->get();

        $this->total = $this->transaccions ? $this->transaccions->sum('importe') : 0;
    }

    public function viewDetails(Transaccion $transaccion)
    {
        $fi = Carbon::parse($this->fromDate)->format('Y-m-d') . ' 00:00:00';
        $ff = Carbon::parse($this->toDate)->format('Y-m-d') . ' 23:59:59';

        $this->details = Transaccion::join('origen_motivos as om', 'transaccions.origen_motivo_id', 'om.id')
            ->join('clientes as c', 'c.id', 'transaccions.cliente_id')
            ->join('origens as ori', 'ori.id', 'om.origen_id')
            ->join('motivos as mot', 'mot.id', 'om.motivo_id')
            ->select(
                'c.cedula as codCliente',
                'transaccions.telefono as TelCliente',
                'c.nombre as nomClient',
                'transaccions.codigo_transf as codigotrans',
                'ori.nombre as origen_nombre',
                'transaccions.id as id',
                'mot.nombre_motivo as motivo_nombre',
                'transaccions.importe',
                'transaccions.created_at as hora',
                'transaccions.observaciones',
                'transaccions.estado as estado'
            )
            ->whereBetween('transaccions.created_at', [$fi, $ff])
            ->where('transaccions.user_id', $this->userid)
            ->where('transaccions.id', $transaccion->id)
            ->get();

        $this->emit('show-modal', 'open modal');
    }

    public function Print()
    {
    }
}
