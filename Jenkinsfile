pipeline {
    agent any
    
    environment {
        DOCKER_COMPOSE_VERSION = '2.21.0'
        APP_NAME = 'laravel-app'
    }
    
    stages {
        stage('Checkout') {
            steps {
                script {
                    checkout scm
                }
            }
        }

        stage('Cleanup Previous Build') {
            steps {
                script {
                    bat '''
                        docker-compose down -v
                        docker rm -f mysql 2>nul || exit 0
                        docker rm -f laravel_app 2>nul || exit 0
                        docker system prune -f
                    '''
                }
            }
        }

        stage('Setup Environment') {
            steps {
                script {
                    // Create .env file from .env.example
                    if (fileExists('.env.example')) {
                        bat 'copy .env.example .env'
                        
                        // Add or modify any environment variables needed
                        bat '''
                            echo DB_CONNECTION=mysql >> .env
                            echo DB_HOST=mysql >> .env
                            echo DB_PORT=3306 >> .env
                            echo DB_DATABASE=kilohealth >> .env
                            echo DB_USERNAME=root >> .env
                            echo DB_PASSWORD= >> .env
                        '''
                    } else {
                        error '.env.example file not found'
                    }
                }
            }
        }
        
        stage('Build') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'docker-compose build --no-cache'
                    } else {
                        bat 'docker-compose build --no-cache'
                    }
                }
            }
        }
        
        stage('Laravel Setup') {
            steps {
                script {
                    if (isUnix()) {
                        sh '''
                            docker-compose up -d
                            sleep 30  # Wait for MySQL to be ready
                            docker-compose exec -T app composer install
                            docker-compose exec -T app cp .env.example .env
                            docker-compose exec -T app php artisan key:generate
                            docker-compose exec -T app php artisan migrate --force
                            docker-compose exec -T app npm install
                            docker-compose exec -T app npm run build
                        '''
                    } else {
                        bat '''
                            docker-compose up -d
                            timeout /t 30
                            docker-compose exec -T app composer install
                            docker-compose exec -T app cp .env.example .env
                            docker-compose exec -T app php artisan key:generate
                            docker-compose exec -T app php artisan migrate --force
                            docker-compose exec -T app npm install
                            docker-compose exec -T app npm run build
                        '''
                    }
                }
            }
        }
        
        stage('Test') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'docker-compose exec -T app php artisan test'
                    } else {
                        bat 'docker-compose exec -T app php artisan test'
                    }
                }
            }
        }
        
        stage('Deploy') {
            when {
                branch 'main'
            }
            steps {
                script {
                    if (isUnix()) {
                        sh 'docker-compose up -d'
                    } else {
                        bat 'docker-compose up -d'
                    }
                }
            }
        }
        
        stage('Cleanup') {
            steps {
                script {
                    if (isUnix()) {
                        sh '''
                            docker-compose down
                            docker system prune -f
                        '''
                    } else {
                        bat '''
                            docker-compose down
                            docker system prune -f
                        '''
                    }
                }
            }
        }
    }
    
    post {
        always {
            cleanWs()
        }
        success {
            echo 'Pipeline completed successfully!'
        }
        failure {
            echo 'Pipeline failed!'
        }
    }
}