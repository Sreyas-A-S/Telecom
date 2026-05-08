<?php

namespace SreyasAS\UnSlayShell\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TerminalController extends Controller
{
    public function index()
    {
        return view('unslay-shell::index');
    }

    public function login(Request $request)
    {
        $password = config('unslay-shell.password', 'admin');

        if ($request->input('password') === $password) {
            session(['terminal_authenticated' => true]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid password']);
    }

    public function execute(Request $request)
    {
        // Check authentication
        if (!session('terminal_authenticated')) {
            return response("Unauthorized. Please refresh and log in.", 401)
                ->header('Content-Type', 'text/plain');
        }

        $command = $request->input('command');
        $cwd = $request->input('cwd', base_path());

        if (empty($command)) {
            return response("", 200)
                ->header('Content-Type', 'text/plain')
                ->header('X-CWD', $cwd);
        }

        $trimmedCommand = trim($command);

        // Handle built-in commands
        if ($trimmedCommand === 'logout' || $trimmedCommand === 'exit') {
            session()->forget('terminal_authenticated');
            return response('Logging out...', 200)
                ->header('Content-Type', 'text/plain')
                ->header('X-Action', 'logout');
        }

        if ($trimmedCommand === 'clear') {
            return response('', 200)
                ->header('Content-Type', 'text/plain')
                ->header('X-Action', 'clear')
                ->header('X-CWD', $cwd);
        }

        // Handle CD
        if ($trimmedCommand === 'cd' || str_starts_with($trimmedCommand, 'cd ')) {
            $path = trim(substr($trimmedCommand, 2));
            $newCwd = $cwd;
            $output = "";

            if (empty($path)) {
                $newCwd = base_path();
            } elseif ($path === '..') {
                $newCwd = dirname($cwd);
            } else {
                if (!str_starts_with($path, '/') && !str_starts_with($path, '\\') && !str_contains($path, ':')) {
                    $newCwd = $cwd . DIRECTORY_SEPARATOR . $path;
                } else {
                    $newCwd = $path;
                }
            }

            if (is_dir($newCwd)) {
                $newCwd = realpath($newCwd);
            } else {
                $output = "bash: cd: $path: No such file or directory\n";
                $newCwd = $cwd; // Revert to old valid CWD
            }

            return response($output, 200)
                ->header('Content-Type', 'text/plain')
                ->header('X-CWD', $newCwd);
        }



        // External Command Execution
        try {
            // Handle 'artisan' alias
            if ($trimmedCommand === 'artisan' || str_starts_with($trimmedCommand, 'artisan ')) {
                $trimmedCommand = 'php ' . $trimmedCommand;
            }

            // Adjust 'php' to use the correct binary
            if (stripos($trimmedCommand, 'php') === 0) {
                $binary = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';

                // Handle CGI vs CLI binary issues
                if (PHP_SAPI === 'cgi-fcgi' || str_contains($binary, 'php-cgi')) {
                    // Try to convert /path/to/php-cgi to /path/to/php
                    $cliBinary = str_replace(['-cgi', '.cgi'], '', $binary);

                    if (@is_executable($cliBinary)) {
                        $binary = $cliBinary;
                    } else {
                        // Fallback: Check if 'php' is in PATH (simple 'php' command)
                        // Or rely on php-cgi with -q (quiet/no-headers) mode
                        $binary = 'php'; // Try standard 'php' first as it's likely in PATH

                        // If we really must use the detected binary and it is cgi, add -q
                        // But we can't easily check 'php' availability here without running it.
                        // Safe bet: If the original binary was absolute and we couldn't find the CLI version,
                        // use the original with -q if it was cgi.
                        if (str_contains($binary, 'php-cgi')) {
                            $binary .= ' -q';
                        }
                    }
                }
                $command = preg_replace('/^php\b/i', '"' . $binary . '"', $trimmedCommand);
            }

            // Capture Environment to ensure PATH is correctly inherited on some execution environments
            $env = getenv();
            $env['FORCE_COLOR'] = '1';
            $env['TERM'] = 'xterm-256color';

            if (PHP_OS_FAMILY === 'Windows') {
                if (!isset($env['SystemRoot'])) {
                    $env['SystemRoot'] = getenv('SystemRoot');
                }
                if (!isset($env['PATH'])) {
                    $env['PATH'] = getenv('PATH');
                }
            }

            $process = Process::fromShellCommandline($command, $cwd, $env);
            $process->setTimeout(60); // Timeout 60s
            $process->run();

            $output = $process->getOutput() . $process->getErrorOutput();

            return response($output, 200)
                ->header('Content-Type', 'text/plain')
                ->header('X-CWD', $cwd);
        } catch (\Exception $e) {
            return response("Error: " . $e->getMessage(), 200)
                ->header('Content-Type', 'text/plain')
                ->header('X-CWD', $cwd);
        }
    }
    public function autocomplete(Request $request)
    {
        // Check authentication
        if (!session('terminal_authenticated')) {
            return response()->json(['matches' => []]);
        }

        $command = $request->input('command');
        $cwd = $request->input('cwd', base_path());

        $parts = explode(' ', $command);
        $lastPart = end($parts);

        $search = $cwd . DIRECTORY_SEPARATOR . $lastPart . '*';
        $matches = glob($search);

        $results = [];
        if ($matches) {
            foreach ($matches as $match) {
                $rel = substr($match, strlen($cwd));
                if (str_starts_with($rel, DIRECTORY_SEPARATOR) || str_starts_with($rel, '/') || str_starts_with($rel, '\\')) {
                    $rel = substr($rel, 1);
                }

                if (is_dir($match)) {
                    $rel .= DIRECTORY_SEPARATOR;
                }
                $results[] = $rel;
            }
        }

        if (count($parts) <= 1) {
            $basicCommands = ['cd', 'clear', 'ls', 'php', 'artisan', 'composer', 'npm', 'git', 'logout', 'exit'];
            foreach ($basicCommands as $cmd) {
                if (str_starts_with($cmd, $lastPart)) {
                    $results[] = $cmd;
                }
            }
        }

        return response()->json(['matches' => array_values(array_unique($results))]);
    }
}
