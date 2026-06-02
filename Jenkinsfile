pipeline {
    agent any

    environment {
        IMAGE_NAME = "minhtri25/pawspa-devops"
        IMAGE_TAG = "${BUILD_NUMBER}"
        CONTAINER_NAME = "pawspa-app"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Install Dependencies') {
            steps {
                sh '''
                                        composer install --no-interaction --prefer-dist || \
                                        composer update doctrine/instantiator phpunit/phpunit --with-all-dependencies --no-interaction --prefer-dist
                    if [ ! -x vendor/bin/phpunit ]; then
                      echo "phpunit not found in lockfile install, running composer update to sync dev dependencies"
                      composer update --no-interaction --prefer-dist
                    fi
                '''
            }
        }

        stage('Composer Validate') {
            steps {
                sh 'composer validate --no-check-lock'
            }
        }

        stage('PHP Syntax Check') {
            steps {
                sh 'find src public -name "*.php" -print0 | xargs -0 -n1 php -l'
            }
        }

        stage('Unit Test') {
            steps {
                sh 'mkdir -p reports'
                sh 'vendor/bin/phpunit --testdox --log-junit reports/junit.xml'
            }
        }

        stage('Code Coverage') {
            steps {
                sh '''
                    mkdir -p coverage
                    if command -v phpdbg >/dev/null 2>&1; then
                      phpdbg -qrr vendor/bin/phpunit --coverage-clover coverage/clover.xml --coverage-text
                    elif php -m | grep -qi xdebug; then
                      XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-clover coverage/clover.xml --coverage-text
                    else
                      echo "Skipping coverage: phpdbg/xdebug not available"
                    fi
                '''
            }
        }

        stage('Docker Build') {
            steps {
                sh 'docker build -t $IMAGE_NAME:$IMAGE_TAG -t $IMAGE_NAME:latest .'
            }
        }

        stage('Docker Login') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'dockerhub-credentials', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                    sh 'echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin'
                }
            }
        }

        stage('Docker Push') {
            steps {
                sh 'docker push $IMAGE_NAME:$IMAGE_TAG'
                sh 'docker push $IMAGE_NAME:latest'
            }
        }

        stage('Deploy') {
            steps {
                // Tạo file .env giả lập từ .env.example vì Git không lưu file .env
                sh 'cp .env.example .env || touch .env'
                
                // Chỉ build và khởi động lại service 'app' (chứa code PHP mới)
                // Phải chỉ định -p du-an-web để Docker hiểu là đang cập nhật project gốc chứ không phải tạo project mới (gây xung đột tên container)
                sh 'docker-compose -p du-an-web up -d --build app'
            }
        }
    }

    post {
        always {
            junit allowEmptyResults: true, testResults: 'reports/junit.xml'
            archiveArtifacts allowEmptyArchive: true, artifacts: 'coverage/clover.xml'
        }
        success {
            echo 'Pipeline completed successfully. Pawspa has been deployed.'
        }
        failure {
            echo 'Pipeline failed. Please check logs.'
        }
    }
}
