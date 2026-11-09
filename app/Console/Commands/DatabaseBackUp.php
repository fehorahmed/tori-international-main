<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;

class DatabaseBackUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = "backup-" . env('DB_DATABASE') . "-" . now()->format('Y-m-d-h-i-s') . ".sql";
        $sqlFilePath = public_path() . "/backup/" . $filename;
        $zipFilename = "backup-" . env('DB_DATABASE') . "-" . now()->format('Y-m-d-h-i-s') . ".zip";
        $zipFilePath = public_path() . "/backup/" . $zipFilename;
        $zipFilePathStore = "backup/" . $zipFilename;

        // Step 1: Run mysqldump and save the .sql file without compression
        $command = '"' . env('MYSQLDUMP_PATH') . '" --user="' . env('DB_USERNAME') .
            '" --password="' . env('DB_PASSWORD') . '" --host="' . env('DB_HOST') .
            '" ' . escapeshellarg(env('DB_DATABASE')) . " > " . $sqlFilePath;

        $returnVar = NULL;
        $output = NULL;

        // Execute the command to create the SQL dump
        exec($command, $output, $returnVar);

        // Step 2: Zip the SQL file
        if (file_exists($sqlFilePath)) {
            // Create a new ZipArchive instance
            $zip = new ZipArchive();

            // Create the zip file
            if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
                // Add the SQL file to the zip
                $zip->addFile($sqlFilePath, $filename);

                // Close the zip archive
                $zip->close();

                // Optional: Delete the original .sql file after zipping if you don't need it
                 unlink($sqlFilePath);
                $backUp = new \App\Models\DatabaseBackup();
                $backUp->create_user_id = auth()->id();
                $backUp->database_path = $zipFilePathStore;
                $backUp->save();

                //echo "Backup successfully created: " . $zipFilePath;
            } else {
                echo "Failed to create ZIP file.";
            }
        } else {
            echo "Failed to create SQL file.";
        }
    }
}
