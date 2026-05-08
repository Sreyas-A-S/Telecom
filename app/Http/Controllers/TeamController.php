<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Zone;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $teams = Team::with('parent')->get();
        $zones = Zone::all(); // Fetch all zones
        if ($request->wantsJson()) {
            return response()->json($teams);
        }
        return view('teams.index', compact('teams', 'zones'));
    }

    public function getDataTableData(Request $request)
    {
        $teams = Team::with('parent')->select('teams.*');

        $dataTable = DataTables::of($teams)
            ->addColumn('parent_name', function (Team $team) {
                return $team->parent->name ?? 'N/A';
            })
            ->addColumn('actions', function (Team $team) {
                $actions = '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#showTeamModal" data-id="' . $team->id . '">View</button>';
                $actions .= ' <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editTeamModal" data-id="' . $team->id . '">Edit</button>';
                $actions .= ' <form action="' . route('teams.destroy', $team->id) . '" method="POST" style="display: inline-block;">'
                    . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger">Delete</button></form>';
                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);

        Log::info('Datatable data:', $dataTable->getData(true));

        return $dataTable;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:teams,id',
            'zones' => 'array',
            'zones.*' => 'exists:zones,id',
            'relationship_type' => 'required_with:zones|in:parent,child',
        ]);

        try {
            $team = Team::create($request->all());

            if ($request->has('zones')) {
                $zonesData = [];
                foreach ($request->zones as $zoneId) {
                    $zonesData[$zoneId] = ['relationship_type' => $request->relationship_type];
                }
                $team->zones()->sync($zonesData);
            }

            if ($request->wantsJson()) {
                return response()->json($team, 201);
            }

            return redirect()->route('teams.index')->with('success', 'Team created successfully!');
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation Failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Error creating team: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Failed to create team.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to create team. Please try again.')->withInput();
        }
    }

    public function show(Request $request, Team $team)
    {
        $team->load('zones'); // Eager load zones
        if ($request->wantsJson()) {
            return response()->json([
                'team' => $team,
                'parent_name' => $team->parent->name ?? ''
            ]);
        }
        return view('teams.show', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'string|max:255',
            'parent_id' => 'nullable|exists:teams,id',
            'zones' => 'array',
            'zones.*' => 'exists:zones,id',
            'relationship_type' => 'required_with:zones|in:parent,child',
        ]);

        $team->update($request->all());

        if ($request->has('zones')) {
            $zonesData = [];
            foreach ($request->zones as $zoneId) {
                $zonesData[$zoneId] = ['relationship_type' => $request->relationship_type];
            }
            $team->zones()->sync($zonesData);
        } else {
            $team->zones()->detach(); // Detach all zones if none are selected
        }

        if ($request->wantsJson()) {
            return response()->json($team, 200);
        }

        return redirect()->route('teams.index');
    }

    public function destroy(Request $request, Team $team)
    {
        $team->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('teams.index');
    }
}
