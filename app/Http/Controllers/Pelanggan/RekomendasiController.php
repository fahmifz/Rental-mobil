<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Services\ItemBasedRecommendationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekomendasiController extends Controller
{
    public function index(
        Request $request,
        ItemBasedRecommendationService $recommendationService
    ): View {
        $recommendations = $recommendationService->recommendForUser(
            $request->user()->id
        );

        $mobilIds = array_values(array_unique(array_map(
            fn (array $recommendation): int => (int) $recommendation['mobil_id'],
            $recommendations
        )));

        $availableMobils = Mobil::query()
            ->whereIn('id', $mobilIds)
            ->where('status', 'tersedia')
            ->get()
            ->keyBy('id');

        $rankedMobils = [];

        foreach ($recommendations as $recommendation) {
            $mobilId = (int) $recommendation['mobil_id'];
            $mobil = $availableMobils->get($mobilId);

            if ($mobil === null) {
                continue;
            }

            $rankedMobils[] = [
                'mobil' => $mobil,
                'predicted_rating' => (float) $recommendation['predicted_rating'],
            ];
        }

        return view('pages.rekomendasi.index', [
            'recommendations' => $rankedMobils,
            'title' => 'Rekomendasi Mobil',
        ]);
    }
}
