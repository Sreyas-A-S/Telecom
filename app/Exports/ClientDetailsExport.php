<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClientDetailsExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $client;
    protected $services;
    protected $uniqueProducts;
    protected $totalInteractions;

    public function __construct($client, $services, $uniqueProducts, $totalInteractions)
    {
        $this->client = $client;
        $this->services = $services;
        $this->uniqueProducts = $uniqueProducts;
        $this->totalInteractions = $totalInteractions;
    }

    public function view(): View
    {
        return view('clients.export_excel', [
            'client' => $this->client,
            'services' => $this->services,
            'uniqueProducts' => $this->uniqueProducts,
            'totalInteractions' => $this->totalInteractions
        ]);
    }

    public function title(): string
    {
        return 'Client Details - ' . $this->client->name;
    }
}
