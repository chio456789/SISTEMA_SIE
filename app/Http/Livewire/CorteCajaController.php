<?php

namespace App\Http\Livewire;

use App\Models\Caja;
use App\Models\Cartera;
use App\Models\CorteCaja;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CorteCajaController extends Component
{
    use WithPagination;
    use WithFileUploads;
    public  $search, $monto, $tipocorte, $caja_id, $user_id, $selected_id;
    public  $pageTitle, $componentName;
    private $pagination = 5;

    public function paginationView()
    {
        return 'vendor.livewire.bootstrap';
    }
    public function mount()
    {
        $this->pageTitle = 'Listado';
        $this->componentName = 'Corte Caja';
        $this->tipocorte = 'Elegir';

        $this->selected_id = 0;
    }
    public function render()
    {
        $idsu = User::join('sucursal_users as su', 'su.user_id', 'users.id')
            ->where('su.user_id', Auth()->user()->id)
            ->select('su.id')
            ->orderBy('su.id', 'desc')
            ->first();
            
        $data = Caja::join('sucursals as s', 's.id', 'cajas.sucursal_id')
            ->join('sucursal_users as su', 'su.sucursal_id', 's.id')
            ->where('su.id', $idsu->id)
            ->select('cajas.*', 's.name as sucursal')
            ->get();


        return view('livewire.cortecaja.component', [
            'data' => $data,

            'carteras' => Cartera::where('caja_id', $this->selected_id)->orderBy('nombre', 'asc')->get()
        ])
            ->extends('layouts.theme.app')
            ->section('content');
    }
    public function getDetails(Caja $caja)
    {
        $this->selected_id = $caja->id;
        $carteras = Cartera::where('caja_id', $caja->id)->count();
        if ($carteras) {
            $this->emit('show-modal', 'details loaded');
        } else {
            $this->emit('no-carteras', 'Esta caja no tiene carteras');
        }
    }
}
