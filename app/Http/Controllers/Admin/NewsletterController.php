<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    /**
     * Muestra la lista de suscriptores al boletín
     */
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('email', 'LIKE', "%{$search}%")
                  ->orWhere('nombre', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('activo', $request->status === 'active');
        }

        $totalSubscribers = NewsletterSubscriber::count();
        $activeSubscribers = NewsletterSubscriber::where('activo', true)->count();
        $inactiveSubscribers = NewsletterSubscriber::where('activo', false)->count();

        $subscribers = $query->orderBy('created_at', 'desc')->paginate(20);
        $subscribers->appends($request->query());

        return view('admin.newsletter.index', compact(
            'subscribers',
            'totalSubscribers',
            'activeSubscribers',
            'inactiveSubscribers'
        ));
    }

    /**
     * Cambia el estado activo/inactivo de un suscriptor
     */
    public function toggleActive($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->activo = !$subscriber->activo;
        $subscriber->save();

        return redirect()->back()->with('success', 'Estado del suscriptor actualizado exitosamente.');
    }

    /**
     * Elimina un suscriptor
     */
    public function destroy($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->delete();

        return redirect()->back()->with('success', 'Suscriptor eliminado correctamente.');
    }

    /**
     * Exporta los suscriptores a CSV
     */
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'suscriptores_boletin_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            
            // BOM UTF-8 para compatibilidad con Excel
            fputs($handle, "\xEF\xBB\xBF");
            
            // Encabezados
            fputcsv($handle, ['ID', 'Email', 'Nombre', 'Estado', 'IP', 'Fecha de Registro']);

            NewsletterSubscriber::orderBy('created_at', 'desc')
                ->chunk(200, function ($subscribers) use ($handle) {
                    foreach ($subscribers as $sub) {
                        fputcsv($handle, [
                            $sub->id,
                            $sub->email,
                            $sub->nombre ?? 'N/A',
                            $sub->activo ? 'Activo' : 'Inactivo',
                            $sub->ip_address ?? 'N/A',
                            $sub->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
