<?php

namespace App\Http\Livewire;

use App\Models\Cartera;
use App\Models\Cliente;
use App\Models\Comision;
use App\Models\Transaccion;
use Exception;
use Illuminate\Support\Carbon;
use Livewire\Component;
use App\Models\Motivo;
use App\Models\Movimiento;
use App\Models\MovTransac;
use App\Models\Origen;
use App\Models\OrigenMotivo;
use App\Models\OrigenMotivoComision;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class TransaccionController extends Component
{
    use WithPagination;

    public $codigo, $importe, $importe2, $utilidad, $costo, $observaciones, $fecha, $origen, $motivo, $codigo_transf, $montoR, $estado,
        $pageTitle, $componentName, $selected_id, $hora, $search, $condicion, $motivA, $check,
        $nombreCliente, $cedula, $celular, $direccion, $email, $fecha_nacim, $razon, $cheq, $nit;

    private $pagination = 10;
    public function paginationView()
    {
        return 'vendor.livewire.bootstrap';
    }

    public function mount()
    {
        $this->pageTitle = 'Transacciones';
        $this->componentName = 'Tigo Money';
        $this->selected_id = 0;
        $this->motivo = 'Elegir';
        $this->origen = 'Elegir';
        $this->hora = date("d-m-y H:i:s ");
        $this->estado = 'Activa';
        $this->montoR = 0;
        $this->direccion = '';
        $this->email = '';
        $this->codigo_transf = 0;
        $this->razon = '';
        $this->nit = 0;
        $this->importe = 0;
        $this->cheq = 0;
        $this->utilidad = 0;
        $this->costo = 0;
        $this->nombreCliente = '';
        $this->condicion = 0;
        $this->select = 1;
        $this->motivA = 0;
        $this->check = 0;
    }

    public function render()
    {
        $user_id = Auth()->user()->id;
        $from = Carbon::parse(Carbon::now())->format('Y-m-d') . ' 00:00:00';
        $to = Carbon::parse(Carbon::now())->format('Y-m-d')   . ' 23:59:59';

        if (strlen($this->search) > 0) {
            $data = Transaccion::join('origen_motivos as om', 'transaccions.origen_motivo_id', 'om.id')

                ->join('mov_transacs as mt', 'transaccions.id', 'mt.transaccion_id')
                ->join('movimientos as m', 'm.id', 'mt.movimiento_id')
                ->join('clientes as c', 'c.id', 'm.cliente_id')
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
                ->whereBetween('transaccions.created_at', [$from, $to])
                ->where('m.user_id', $user_id)
                ->where('c.cedula', 'like', '%' . $this->search . '%')
                ->orWhere('ori.nombre', 'like', '%' . $this->search . '%')
                ->orWhere('mot.nombre_motivo', 'like', '%' . $this->search . '%')
                ->orderBy('transaccions.created_at', 'desc')
                ->paginate($this->pagination);
        } else {
            $data = Transaccion::join('origen_motivos as om', 'transaccions.origen_motivo_id', 'om.id')

                ->join('mov_transacs as mt', 'transaccions.id', 'mt.transaccion_id')
                ->join('movimientos as m', 'm.id', 'mt.movimiento_id')
                ->join('clientes as c', 'c.id', 'm.cliente_id')
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
                ->whereBetween('transaccions.created_at', [$from, $to])
                ->where('m.user_id', $user_id)

                ->orderBy('transaccions.created_at', 'desc')

                ->paginate($this->pagination);
        }
        $datos = [];
        if (strlen($this->cedula) > 0) {
            $datos = Cliente::where('cedula', 'like', '%' . $this->cedula . '%')->orderBy('cedula', 'desc')->get();
            if ($datos->count() > 0) {
                $this->condicion = 1;
            } else {
                $this->condicion = 0;
            }
            if ($this->select == 0) {
                $this->condicion = 0;
            }
        } else {
            if ($this->select == 0)
                $this->select = 1;
        }

        if ($this->origen != 'Elegir') {
            $mot = OrigenMotivo::join('motivos as m', 'm.id', 'origen_motivos.motivo_id')
                ->where('origen_motivos.origen_id', $this->origen)->pluck('m.id')->toArray();
            $motivos = Motivo::find($mot);
        } else {
            $motivos = [];
        }
        /* Mostrar label e imput (teléfono solicitante) solo si el motivo es de tipo Abono */
        if ($this->origen != 'Elegir' && $this->motivo != 'Elegir') {
            $motiv = Motivo::find($this->motivo);
            if ($motiv->tipo == 'Abono') {
                $this->motivA = 1;
            } else {
                $this->motivA = 0;
            }
        }
        if ($this->check == 0) {
            $this->montoR = $this->importe;
        } else {
            $this->Comision(true);
        }


        return view('livewire.transaccion.component', [
            'data' => $data,
            'origenes' => Origen::orderBy('nombre', 'asc')->get(),
            'motivos' => $motivos,
            'datos' => $datos,
        ])
            ->extends('layouts.theme.app')
            ->section('content');
    }
    /* Cargar los datos seleccionados en la tabla a los label */
    public function Seleccionar($cedula, $celular)
    {
        $this->cedula = $cedula;
        $this->celular = $celular;
        $this->select = 0;
    }

    public function Utilidad($monto, $costo)
    {
        $calcUtilidad = $monto - $costo;
        return $calcUtilidad;
    }
    /* Calcular Comision */
    public function Comision($state)
    {
        if ($state) {
            $this->importe2 = $this->importe;
            $idsOrigesMots = OrigenMotivo::join('motivos as m', 'origen_motivos.motivo_id', 'm.id')
                ->join('origens as o', 'origen_motivos.origen_id', 'o.id')
                ->where('o.id', $this->origen)
                ->where('m.id', $this->motivo)->pluck('origen_motivos.id')->toArray();


            $lista = OrigenMotivoComision::join('comisions as c', 'origen_motivo_comisions.comision_id', 'c.id')
                ->where('c.monto_inicial', '<=', $this->importe2)
                ->where('c.monto_final', '>=', $this->importe2)
                ->where('origen_motivo_comisions.origen_motivos_id', $idsOrigesMots[0])
                ->where('c.tipo', 'Cliente')
                ->pluck('c.id')->toArray();

            try {
                $comis = Comision::find($lista[0]);
            } catch (Exception $e) {
                $this->emit('item-error', "Selecione una comisión para este motivo");
                return;
            }

            if ($comis->porcentaje == 0) {
                if ($comis->monto_a == 'Monto a Cobrar') {
                    $this->importe = $this->importe + $comis->comision;
                }
                if ($comis->monto_a == 'Monto a Registrar') {
                    $this->montoR = $this->importe + $comis->comision;
                }
                if ($comis->monto_a == 'Ambas') {
                    $this->importe = $this->importe + $comis->comision;
                    $this->montoR = $this->importe;
                }
            } else {

                if ($comis->monto_a == 'Monto a Cobrar') {

                    $this->importe = ($this->importe * $comis->comision) / 100 + $this->importe;
                }
                if ($comis->monto_a == 'Monto a Registrar') {
                    $this->montoR =  ($this->importe * $comis->comision) / 100 + $this->importe;
                }
                if ($comis->monto_a == 'Ambas') {

                    $this->importe = ($this->importe * $comis->comision) / 100 + $this->importe;
                    $this->montoR = $this->importe;
                }
            }
            $this->check = 1;
        } else {


            $this->importe = $this->importe2;
            $this->montoR = $this->importe2;
            $this->check = 0;
        }
    }
    /* Registrar una transaccion */
    public function Store()
    {
        $rules = [ /* Reglas de validacion */
            'cedula' => 'required|min:5',
            'celular' => 'required|integer|min:8',
            'motivo' => 'required|not_in:Elegir',
            'origen' => 'required|not_in:Elegir',
            'importe' => 'required|integer|min:1|not_in:0'
        ];
        $messages = [ /* mensajes de validaciones */
            'cedula.required' => 'Ingresa el número de carnet de identidad',
            'cedula.min' => 'El numero de carnet debe tener al menos 3 caracteres',
            'celular.required' => 'Ingresa el telefono del solicitante',
            'celular.min' => 'El teléfono debe tener al menos 8 caractéres',
            'celular.integer' => 'El teléfono debe ser un número',
            'motivo.not_in' => 'Seleccione un valor distinto a Elegir',
            'origen.not_in' => 'Seleccione un valor distinto a Elegir',
            'importe.required' => 'Ingrese un monto válido',
            'importe.min' => 'Ingrese un monto mayor a 0',
            'importe.not_in' => 'Ingrese un monto válido',
            'importe.integer' => 'El monto debe ser un número'
        ];

        $this->validate($rules, $messages);
        /* obtener id del motivo-origen seleccionado */
        $idsOrigesMots = OrigenMotivo::where('motivo_id', $this->motivo)
            ->where('origen_id', $this->origen)
            ->get();
        /* Obtener al cliente con la cedula */
        $listaCL = Cliente::where('cedula', $this->cedula)
            ->get()
            ->first();
        DB::beginTransaction();
        try {
            if ($listaCL->count() > 0) { /* Actualizar telefono del cliente */
                if ($listaCL->celular != $this->celular) {
                    $listaCL->celular = $this->celular;
                    $listaCL->save();
                }
            } else { /* Registrar un nuevo cliente */
                $listaCL = Cliente::create([
                    'nombre' => $this->nombreCliente,
                    'cedula' => $this->cedula,
                    'celular' => $this->celular,
                    'direccion' => $this->direccion,
                    'email' => $this->email,
                    'fecha_nacim' => $this->fecha_nacim,
                    'razon_social' => $this->razon,
                    'nit' => $this->nit
                ]);
                $listaCL->save();
            }
            
            $origenNombre = Origen::where('id', $this->origen)->get()->first();
            $cartera = Cartera::where('nombre', $origenNombre->nombre)->get()->first();
            Movimiento::create([
                'type' => 'EGRESO',
                'status' => 'ACTIVO',
                'import' => $this->importe,
                'user_id' => Auth()->user()->id,
                'cliente_id' => $listaCL->id,
                'cartera_id' => $cartera->id
            ]);

            /* Crear nueva transaccion */
            Transaccion::create([
                'codigo_transf' => $this->codigo_transf,
                'importe' => $this->importe,
                'utilidad' => $this->utilidad,
                'costo' => $this->costo,
                'observaciones' => $this->observaciones,
                'fecha_transaccion' => $this->hora,
                'estado' => $this->estado,
                'telefono' => $this->celular,
                'origen_motivo_id' => $idsOrigesMots[0]->id
            ]);
            $idtransaccion = Transaccion::orderBy('id', 'desc')->first();
            $idmovimiento = Movimiento::orderBy('id', 'desc')->first();

            MovTransac::create([
                'movimiento_id' => $idmovimiento->id,
                'transaccion_id' => $idtransaccion->id
            ]);

            DB::commit();
            
            $this->resetUI();
            $this->emit('item-added', 'Transaccion Registrada');
        } catch (Exception $e) {
            DB::rollback();
            $this->emit('item-error', 'No se pudo realizar la transaccion ' . $e->getMessage());
        }
    }

    protected $listeners = ['deleteRow' => 'Anular'];
    /* Anular una transacción */
    public function Anular($id)
    {
        $tran = Transaccion::find($id);
        $tran->estado = 'Anulada';
        $tran->save();
        $this->emit('item-anulado', 'Se anuló la transacción');
    }

    /* Resetear los imput */
    public function resetUI()
    {
        $this->selected_id = 0;
        $this->cedula = '';
        $this->celular = '';
        $this->motivo = 'Elegir';
        $this->origen = 'Elegir';
        $this->hora = date("d-m-y H:i:s ");
        $this->estado = 'Activa';
        $this->montoR = 0;
        $this->direccion = '';
        $this->email = '';
        $this->codigo_transf = 0;
        $this->razon = '';
        $this->nit = 0;
        $this->importe = 0;
        $this->cheq = 0;
        $this->utilidad = 0;
        $this->costo = 0;
        $this->nombreCliente = '';
        $this->condicion = 0;
        $this->select = 1;
        $this->motivA = 0;
        $this->check = 0;
        $this->resetValidation();
        $this->resetPage();
    }
}
