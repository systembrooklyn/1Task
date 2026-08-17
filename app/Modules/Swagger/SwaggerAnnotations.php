<?php

namespace App\Modules\Swagger;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="1Task API Documentation"
 *     ),
 *     @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="Target Environment Server Path"
 *     ),
 *     @OA\Server(
 *         url="http://127.0.0.1:8000",
 *         description="Local Development Server"
 *     ),
 *     @OA\Server(
 *         url="https://starfish-app-gv3mu.ondigitalocean.app",
 *         description="Live Production Server"
 *     ),
 *     tags={
 *         @OA\Tag(name="Authentication", description="Auth endpoints"),
 *         @OA\Tag(name="User Profile", description="User profile management"),
 *         @OA\Tag(name="User Management", description="Admin user management"),
 *         @OA\Tag(name="Dashboard", description="User dashboard analytics"),
 *         @OA\Tag(name="Invitations", description="User invitations"),
 *         @OA\Tag(name="Company Plan", description="Company plan management"),
 *         @OA\Tag(name="User", description="General user operations"),
 *         @OA\Tag(name="User Department", description="Department assignments"),
 *
 *         @OA\Tag(name="Plans", description="Endpoints for managing and retrieving subscription plans"),
 *         @OA\Tag(name="Features", description="Endpoints for feature management"),
 *
 *         @OA\Tag(name="Tasks", description="Operations for managing tasks, status, and metadata"),
 *         @OA\Tag(name="Task Attachments", description="Manage file uploads and downloads for tasks"),
 *         @OA\Tag(name="Task Comments", description="Endpoints for task comments and threaded replies"),
 *         @OA\Tag(name="Task Revisions", description="Tracking edits and history logs for tasks"),
 *
 *         @OA\Tag(name="Daily Task", description="Daily task operations"),
 *         @OA\Tag(name="Daily Task Report", description="Daily task reporting"),
 *         @OA\Tag(name="Daily Task Evaluation", description="Task evaluations and performance"),
 * 
 *         @OA\Tag(name="Permissions", description="Project Permissions"),
 *         @OA\Tag(name="Roles", description="Manage Roles"),
 * 
 *         @OA\Tag(name="Digital Card Auth", description="Authentication for digital card users"),
 *         @OA\Tag(name="Digital Card", description="Public digital card viewing and management"),
 *
 *     }
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="AdminToken",
 *     type="apiKey",
 *     in="header",
 *     name="X-Admin-Token",
 *     description="Enter custom admin token here"
 * )
 */
class SwaggerAnnotations
{
    // Global OpenAPI annotations
}
